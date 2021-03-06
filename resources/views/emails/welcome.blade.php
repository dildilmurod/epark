@component('mail::message')
    Здравствуйте,
    Благодарим Вас за регистрацию на портале Epark.uz. Чтобы завершить регистрацию, нажмите на следующую кнопку.
    @component('mail::button', ['url' => $url])
        Верифицировать
    @endcomponent

    Если Вы не можете пройти по ссылке, скопируйте ссылку в окно вашего браузера или введите непосредственно с клавиатуры.

    {{ $url }}

    Если вы не зарегистрировали аккаунт на Epark.uz, пожалуйста, проигнорируйте это сообщение.
    С уважением,
    IT Park

@endcomponent
