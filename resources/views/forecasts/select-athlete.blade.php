<div style="border: 1px solid grey;border-radius: 5px;padding: 5px;">
<span style="font-weight: bold">Choose athlete from season <a
        href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">24/25</a></span>

    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
        Choose athlete:</h1>

    <div class="flex flex-row-reverse">
    </div>

    <div class="px-2 py-2">
        <form method="POST" action="https://www.biatlons.kilograms.lv/login" class="mb-4 space-y-2">
            <input type="hidden" name="_token" value="c4oyW2LK2Cjlvf0ObNu2q4JSYoAO9YjWjN2eW3Rb" autocomplete="off">
            <div class="space-y-1">
                <label for="email" class="text-gray-500">Country</label>
                <select type="email" name="email" value="" required="" autofocus="" autocomplete="email"
                        class="px-3 py-2 transition duration-150 ease-in-out border border-gray-300 rounded-md appearance-none focus:outline-none focus:shadow-outline focus:border-blue-300">
                    <option value="All">All countries</option>
                    <option value="Norway">Norway</option>
                    <option value="Latvia">Latvia</option>
                </select>
            </div>

            <div class="space-y-1">
                <label for="password" class="text-gray-500">Speed (max)</label>
                <input type="password" name="password" required="" autocomplete="current-password"
                       class="px-3 py-2 transition duration-150 ease-in-out border border-gray-300 rounded-md appearance-none focus:outline-none focus:shadow-outline focus:border-blue-300">
            </div>

            <div class="space-y-1">
                <label for="password1" class="text-gray-500">Stand shooting (min)</label>
                <input type="password" name="password1" required="" autocomplete="current-password"
                       class="px-3 py-2 transition duration-150 ease-in-out border border-gray-300 rounded-md appearance-none focus:outline-none focus:shadow-outline focus:border-blue-300">
            </div>

            <div class="space-y-1">
                <label for="password2" class="text-gray-500">Prone shooting (min)</label>
                <input type="password" name="password2" required="" autocomplete="current-password"
                       class="px-3 py-2 transition duration-150 ease-in-out border border-gray-300 rounded-md appearance-none focus:outline-none focus:shadow-outline focus:border-blue-300">
            </div>

        </form>


    </div>
    <table border="1" style="border-collapse: collapse;"
           class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
        <thead>
        <tr>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Favourite
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                WCh points
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Discipline points
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Name
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Country
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Speed, %
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Standing, %
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Prone, %
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Gold medals
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Silver medals
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Bronze medals
            </th>
            <th scope="col"
                class="px-2 py-2 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-500">
                Top 10
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($athletes as $athlete)
        <tr class="odd:bg-white even:bg-gray-100">
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">⭐</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">4999</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">189</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">{{$athlete->given_name}} {{$athlete->family_name}}</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09"><img src="{{$athlete->flag_uri}}"
                                                                                       style="height:20px;display:inline-block;"></a>&nbsp;<a
                    href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">{{$athlete->nat}}</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">0%</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">90%</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">90%</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">5</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">3</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">3</a>

            </td>
            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                <a href="https://www.biatlons.kilograms.lv/events/BT2425SWRLCP09">21</a>

            </td>

        </tr>
        @endforeach
        </tbody>
    </table>
</div>
