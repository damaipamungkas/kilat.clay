<div class="icon-menu" style="display: flex; flex-direction: row; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 15px; width: 100%; max-width: 800px; margin: 10px auto; box-sizing: border-box;">

    <!-- ITEM 1 -->
    <div class="icon-item" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;" onclick="window.location.href='{{ route('knowledge') }}'">
        <div class="icon-circle">
            <i class="fa-solid fa-book"></i>
        </div>
        <span>Pengetahuan</span>
    </div>

    <!-- ITEM 2 -->
    <div class="icon-item" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;" onclick="window.location.href='{{ route('schedule') }}'">
        <div class="icon-circle">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <span>Jadwal</span>
    </div>

    <!-- ITEM 3 -->
    <div class="icon-item" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;" onclick="window.location.href='{{ route('rate') }}'">
        <div class="icon-circle">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <span>Tarif</span>
    </div>

    <!-- ITEM 4 -->
    <div class="icon-item" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;" onclick="window.location.href='{{ route('faq') }}'">
        <div class="icon-circle">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <span>FAQ</span>
    </div>

    <!-- ITEM 5 -->
    <div class="icon-item" style="display: flex; flex-direction: column; align-items: center; cursor: pointer;" onclick="window.location.href='{{ route('feedback') }}'">
        <div class="icon-circle">
            <i class="fa-solid fa-comment-dots"></i>
        </div>
        <span>Umpan Balik</span>
    </div>

</div>
