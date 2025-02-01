@php
    $isCompleted = $isCompleted ?? false;
@endphp

<i class="cursor-pointer fa-circle @if($isCompleted) fa-regular text-gray-500  bg-white @else fas @endif"></i>
