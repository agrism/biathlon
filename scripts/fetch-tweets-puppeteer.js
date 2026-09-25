#!/usr/bin/env node

/**
 * Headless Puppeteer Twitter Syndication & RSS Scraper with Stealth Plugin
 * Usage:
 *   node scripts/fetch-tweets-puppeteer.js --handle=penaltyloop
 *   node scripts/fetch-tweets-puppeteer.js --rss=https://penaltyloop.com/feed/
 *   node scripts/fetch-tweets-puppeteer.js --all
 */

import fs from 'node:fs';
import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';

puppeteer.use(StealthPlugin());

const DEFAULT_HANDLES = ['penaltyloop', 'biathstats', 'biathlonworld', 'ibu_newsroom', 'BiathlonLivefr', 'NordicMag'];
const DEFAULT_RSS = 'https://penaltyloop.com/feed/';

// Parse command line arguments
function parseArgs() {
    const args = process.argv.slice(2);
    const options = {
        handle: null,
        rss: null,
        all: false,
        timeout: 25000,
    };

    for (const arg of args) {
        if (arg === '--all') {
            options.all = true;
        } else if (arg.startsWith('--handle=')) {
            options.handle = arg.split('=')[1].replace(/^@/, '').trim();
        } else if (arg.startsWith('--rss=')) {
            options.rss = arg.split('=')[1].trim();
        } else if (arg.startsWith('--timeout=')) {
            options.timeout = parseInt(arg.split('=')[1], 10) || 25000;
        }
    }

    return options;
}

// Launch browser with lightweight flags and resource interception
async function createBrowser() {
    const wsEndpoint = process.env.PUPPETEER_WS_ENDPOINT;
    let executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;

    if (!executablePath) {
        const possiblePaths = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ];
        for (const p of possiblePaths) {
            if (fs.existsSync(p)) {
                executablePath = p;
                break;
            }
        }
    }

    const launchArgs = [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-accelerated-2d-canvas',
        '--no-first-run',
        '--no-zygote',
        '--single-process',
        '--disable-background-networking',
        '--disable-default-apps',
        '--disable-extensions',
        '--disable-sync',
        '--disable-translate',
        '--hide-scrollbars',
        '--metrics-recording-only',
        '--mute-audio',
        '--safebrowsing-disable-auto-update',
        '--window-size=1280,800',
    ];

    if (wsEndpoint) {
        return await puppeteer.connect({ browserWSEndpoint: wsEndpoint });
    }

    return await puppeteer.launch({
        headless: 'new',
        executablePath: executablePath || undefined,
        args: launchArgs,
    });
}

// Configure page to block media, fonts, images for speed & low memory
async function setupOptimizedPage(browser) {
    const page = await browser.newPage();

    await page.setViewport({ width: 1280, height: 800 });
    await page.setUserAgent(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
    );

    await page.setExtraHTTPHeaders({
        'Accept-Language': 'en-US,en;q=0.9',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Referer': 'https://platform.twitter.com/',
    });

    await page.setRequestInterception(true);
    page.on('request', (req) => {
        const resourceType = req.resourceType();
        if (['image', 'font', 'media'].includes(resourceType)) {
            req.abort();
        } else {
            req.continue();
        }
    });

    return page;
}

// Helper: Fetch rich tweet metadata by status ID
async function fetchTweetById(id, fallbackHandle = '') {
    try {
        const res = await fetch(`https://cdn.syndication.twimg.com/tweet-result?id=${id}&token=1`, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept': 'application/json, text/plain, */*',
                'Referer': 'https://platform.twitter.com/',
                'Origin': 'https://platform.twitter.com',
            }
        });

        if (!res.ok) {
            return null;
        }

        const tweet = await res.json();
        if (!tweet || !tweet.id_str) {
            return null;
        }

        let text = tweet.text || '';
        // Expand t.co links
        const urls = tweet.entities?.urls || [];
        for (const urlEntity of urls) {
            if (urlEntity.url && urlEntity.expanded_url) {
                text = text.replaceAll(urlEntity.url, urlEntity.expanded_url);
            }
        }

        const mediaUrls = [];
        if (Array.isArray(tweet.photos)) {
            for (const p of tweet.photos) {
                if (p.url) mediaUrls.push(p.url);
            }
        }
        if (Array.isArray(tweet.mediaDetails)) {
            for (const m of tweet.mediaDetails) {
                if (m.media_url_https && !mediaUrls.includes(m.media_url_https)) {
                    mediaUrls.push(m.media_url_https);
                }
            }
        }
        if (tweet.card && tweet.card.binding_values) {
            const bv = tweet.card.binding_values;
            const cardImg = bv.thumbnail_image_large?.image_value?.url_https
                || bv.photo_image_full_size_large?.image_value?.url_https
                || bv.thumbnail_image?.image_value?.url_https
                || bv.summary_photo_image_large?.image_value?.url_https
                || bv.promo_image?.image_value?.url_https;
            if (cardImg && !mediaUrls.includes(cardImg)) {
                mediaUrls.push(cardImg);
            }
        }

        const authorHandle = tweet.user?.screen_name || fallbackHandle;
        const idStr = tweet.id_str || id;

        return {
            tweet_id: `tw_${idStr}`,
            author_name: tweet.user?.name || authorHandle,
            author_handle: authorHandle,
            author_avatar: tweet.user?.profile_image_url_https || null,
            content: text,
            media_urls: mediaUrls.length > 0 ? mediaUrls : null,
            likes_count: tweet.favorite_count || 0,
            retweets_count: tweet.retweet_count || 0,
            tweet_url: `https://x.com/${authorHandle}/status/${idStr}`,
            published_at: tweet.created_at || new Date().toISOString(),
        };
    } catch (e) {
        return null;
    }
}

// Scrape Twitter timeline for a single handle
async function scrapeTwitterHandle(page, handle, timeout = 25000) {
    const results = [];
    const tweetIds = new Set();
    const domItems = new Map();

    // Strategy 1: Direct x.com profile scraping
    try {
        const profileUrl = `https://x.com/${handle}`;
        await page.goto(profileUrl, {
            waitUntil: 'networkidle2',
            timeout: timeout,
        }).catch(() => {});

        // Wait up to 6 seconds for tweets/articles to appear
        await page.waitForSelector('article, a[href*="/status/"]', { timeout: 6000 }).catch(() => {});

        const extracted = await page.evaluate((targetHandle) => {
            const found = [];
            const articles = document.querySelectorAll('article');

            articles.forEach((art) => {
                const link = art.querySelector('a[href*="/status/"]');
                const href = link ? link.getAttribute('href') : '';
                const match = href.match(/\/([a-zA-Z0-9_]+)\/status\/(\d+)/);
                if (match) {
                    const id = match[2];
                    const textEl = art.querySelector('[data-testid="tweetText"]');
                    const timeEl = art.querySelector('time');
                    const imgEls = art.querySelectorAll('[data-testid="tweetPhoto"] img, [data-testid="card.wrapper"] img, [data-testid="card.layoutLarge.media"] img, img[src*="pbs.twimg.com/media"], img[src*="pbs.twimg.com/card_img"]');
                    const media = Array.from(imgEls).map(img => img.src).filter(Boolean);

                    found.push({
                        id: id,
                        author_handle: match[1] || targetHandle,
                        text: textEl ? textEl.innerText : art.innerText.substring(0, 200),
                        published_at: timeEl ? timeEl.getAttribute('datetime') : null,
                        media_urls: media.length > 0 ? media : null,
                    });
                }
            });

            // Fallback for any status links in the DOM
            const links = document.querySelectorAll('a[href*="/status/"]');
            links.forEach((l) => {
                const href = l.getAttribute('href') || '';
                const match = href.match(/\/([a-zA-Z0-9_]+)\/status\/(\d+)/);
                if (match && !found.some(f => f.id === match[2])) {
                    found.push({
                        id: match[2],
                        author_handle: match[1] || targetHandle,
                        text: '',
                        published_at: null,
                        media_urls: null,
                    });
                }
            });

            return found;
        }, handle);

        for (const item of extracted) {
            tweetIds.add(item.id);
            if (item.text) {
                domItems.set(item.id, item);
            }
        }
    } catch (e) {
        // Strategy 1 navigation or evaluation error
    }

    // For all tweet IDs discovered, fetch rich official data from tweet-result endpoint
    for (const id of Array.from(tweetIds).slice(0, 15)) {
        const enriched = await fetchTweetById(id, handle);
        if (enriched) {
            results.push(enriched);
        } else if (domItems.has(id)) {
            const dom = domItems.get(id);
            results.push({
                tweet_id: `tw_${id}`,
                author_name: dom.author_handle || handle,
                author_handle: dom.author_handle || handle,
                author_avatar: null,
                content: dom.text || '',
                media_urls: dom.media_urls,
                likes_count: 0,
                retweets_count: 0,
                tweet_url: `https://x.com/${dom.author_handle || handle}/status/${id}`,
                published_at: dom.published_at || new Date().toISOString(),
            });
        }
    }

    if (results.length > 0) {
        return {
            success: true,
            handle: handle,
            count: results.length,
            items: results,
        };
    }

    // Strategy 2: Legacy syndication widget fallback
    try {
        const syndicationUrl = `https://syndication.twitter.com/srv/timeline-profile/screen-name/${handle}`;
        const response = await page.goto(syndicationUrl, {
            waitUntil: 'domcontentloaded',
            timeout: timeout,
            referer: 'https://platform.twitter.com/',
        });

        if (response && response.status() === 200) {
            const data = await page.evaluate(() => {
                const scriptTag = document.getElementById('__NEXT_DATA__');
                if (scriptTag && scriptTag.textContent) {
                    try {
                        const parsed = JSON.parse(scriptTag.textContent);
                        return parsed?.props?.pageProps?.timeline?.entries || [];
                    } catch (e) {}
                }
                return [];
            });

            for (const entry of data) {
                const tweet = entry.content?.tweet;
                if (!tweet) continue;

                let text = tweet.full_text || tweet.text || '';
                if (!text.trim()) continue;

                const urls = tweet.entities?.urls || [];
                for (const urlEntity of urls) {
                    if (urlEntity.url && urlEntity.expanded_url) {
                        text = text.replaceAll(urlEntity.url, urlEntity.expanded_url);
                    }
                }

                const idStr = tweet.id_str || tweet.id;
                if (!idStr) continue;

                const user = tweet.user || {};
                const createdAt = tweet.created_at || new Date().toISOString();

                const mediaUrls = [];
                const extMedia = tweet.extended_entities?.media || tweet.entities?.media || [];
                for (const m of extMedia) {
                    if (m.media_url_https) {
                        mediaUrls.push(m.media_url_https);
                    }
                }

                results.push({
                    tweet_id: `tw_${idStr}`,
                    author_name: user.name || handle,
                    author_handle: user.screen_name || handle,
                    author_avatar: user.profile_image_url_https || null,
                    content: text,
                    media_urls: mediaUrls.length > 0 ? mediaUrls : null,
                    likes_count: tweet.favorite_count || 0,
                    retweets_count: tweet.retweet_count || 0,
                    tweet_url: `https://x.com/${user.screen_name || handle}/status/${idStr}`,
                    published_at: createdAt,
                });
            }
        }
    } catch (e) {}

    return {
        success: results.length > 0,
        handle: handle,
        count: results.length,
        error: results.length === 0 ? `Could not fetch tweets for @${handle}` : null,
        items: results,
    };
}

// Scrape / Fetch WAF-protected RSS Feed
async function scrapeRssFeed(page, rssUrl, timeout = 25000) {
    try {
        const response = await page.goto(rssUrl, {
            waitUntil: 'domcontentloaded',
            timeout: timeout,
        });

        const status = response ? response.status() : 0;
        const pageContent = await page.content();
        
        // Also extract raw text in case browser displayed XML source
        const rawText = await page.evaluate(() => document.body?.innerText || '');
        const xmlContent = rawText.includes('<rss') || rawText.includes('<channel>') ? rawText : pageContent;

        // Parse XML items using simple regex for robust, dependency-free parsing
        const itemRegex = /<item>([\s\S]*?)<\/item>/gi;
        const items = [];
        let match;

        while ((match = itemRegex.exec(xmlContent)) !== null) {
            const itemBlock = match[1];

            const titleMatch = /<title>(?:<!\[CDATA\[(.*?)\]\]>|(.*?))<\/title>/is.exec(itemBlock);
            const title = (titleMatch ? (titleMatch[1] || titleMatch[2]) : '').trim();

            const descMatch = /<description>(?:<!\[CDATA\[(.*?)\]\]>|(.*?))<\/description>/is.exec(itemBlock);
            let desc = (descMatch ? (descMatch[1] || descMatch[2]) : '').trim();
            // Strip HTML tags from description
            desc = desc.replace(/<\/?[^>]+(>|$)/g, '');

            const linkMatch = /<link>(?:<!\[CDATA\[(.*?)\]\]>|(.*?))<\/link>/is.exec(itemBlock);
            const link = (linkMatch ? (linkMatch[1] || linkMatch[2]) : '').trim();

            const pubDateMatch = /<pubDate>(?:<!\[CDATA\[(.*?)\]\]>|(.*?))<\/pubDate>/is.exec(itemBlock);
            const pubDate = (pubDateMatch ? (pubDateMatch[1] || pubDateMatch[2]) : '').trim();

            const enclosureMatch = /<enclosure[^>]+url=["']([^"']+)["']/i.exec(itemBlock);
            const mediaUrl = enclosureMatch ? enclosureMatch[1] : null;

            if (title || desc) {
                const textContent = `📝 ${title}${desc ? "\n" + desc.substring(0, 240) + '...' : ''}`;
                const slugMatch = link.match(/\/([^\/]+)\/?$/);
                const slug = slugMatch ? slugMatch[1] : Buffer.from(link).toString('base64').substring(0, 16);

                items.push({
                    tweet_id: `article_${slug}`,
                    author_name: 'Penalty Loop',
                    author_handle: 'penaltyloop',
                    author_avatar: 'https://pbs.twimg.com/profile_images/2084999188614373376/QytLH4Fk_normal.jpg',
                    content: textContent,
                    media_urls: mediaUrl ? [mediaUrl] : null,
                    likes_count: 0,
                    retweets_count: 0,
                    tweet_url: link || 'https://penaltyloop.com',
                    published_at: pubDate || new Date().toISOString(),
                });
            }
        }

        return {
            success: true,
            url: rssUrl,
            status: status,
            count: items.length,
            items: items,
        };
    } catch (err) {
        return {
            success: false,
            url: rssUrl,
            error: err.message || 'Unknown error while fetching RSS feed',
            items: [],
        };
    }
}

// Main Runner
async function main() {
    const opts = parseArgs();
    const output = {
        timestamp: new Date().toISOString(),
        results: {},
        errors: [],
    };

    let browser = null;
    try {
        browser = await createBrowser();
        const page = await setupOptimizedPage(browser);

        if (opts.handle) {
            const res = await scrapeTwitterHandle(page, opts.handle, opts.timeout);
            output.results[opts.handle] = res;
            if (!res.success && res.error) output.errors.push(res.error);
        } else if (opts.rss) {
            const res = await scrapeRssFeed(page, opts.rss, opts.timeout);
            output.results['rss'] = res;
            if (!res.success && res.error) output.errors.push(res.error);
        } else if (opts.all) {
            // Scrape all handles
            for (const handle of DEFAULT_HANDLES) {
                const res = await scrapeTwitterHandle(page, handle, opts.timeout);
                output.results[handle] = res;
                if (!res.success && res.error) output.errors.push(`${handle}: ${res.error}`);
            }

            // Scrape default RSS
            const rssRes = await scrapeRssFeed(page, DEFAULT_RSS, opts.timeout);
            output.results['penaltyloop_rss'] = rssRes;
            if (!rssRes.success && rssRes.error) output.errors.push(`RSS: ${rssRes.error}`);
        } else {
            // Default: scrape single handle penaltyloop
            const res = await scrapeTwitterHandle(page, 'penaltyloop', opts.timeout);
            output.results['penaltyloop'] = res;
            if (!res.success && res.error) output.errors.push(res.error);
        }
    } catch (globalErr) {
        output.errors.push(`Global Puppeteer Error: ${globalErr.message}`);
    } finally {
        if (browser) {
            await browser.close();
        }
    }

    // Output strictly JSON to stdout for Laravel parser
    console.log(JSON.stringify(output, null, 2));
}

main();

