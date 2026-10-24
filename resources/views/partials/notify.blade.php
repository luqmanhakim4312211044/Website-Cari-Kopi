{{--
    Semua notifikasi tampil sebagai pop-up (SweetAlert2) lewat public/js/app.js:
    pesan flash sukses/gagal, daftar error validasi, dan pesan di bawah tiap kolom yang salah.
--}}
<div id="flash" hidden
     @if (session('error')) data-error="{{ session('error') }}" @endif
     @if (session('success') || session('status')) data-success="{{ session('success') ?? session('status') }}" @endif
     @if ($errors->any())
         data-errors="{{ json_encode($errors->all(), JSON_UNESCAPED_UNICODE) }}"
         data-field-errors="{{ json_encode(collect($errors->messages())->map(fn ($messages) => $messages[0])->all(), JSON_UNESCAPED_UNICODE) }}"
     @endif></div>
