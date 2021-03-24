@component('mail::message')
    Здравствуйте,
    Благодарим Вас за ползованием портала Epark.uz. Вы просили обнулить пароль от аккаунта платформы.

    Ваш временный пароль: {{ $passtext }}

    Чтобы активировать пароль переходите по ссылке ниже
    @component('mail::button', ['url' => $url])
        Активировать пароль
    @endcomponent

    Если Вы не можете пройти по ссылке, скопируйте ссылку в окно вашего браузера или введите непосредственно с клавиатуры.
    {{ $url }}
    После того как зашли в аккаунт с временным паролем, пожалуйста поменяйте его.

    Если вы не зарегистрировали аккаунт на Epark.uz и не просили обнулить пароль, пожалуйста, проигнорируйте это сообщение.
    С уважением,
    IT Park
@endcomponent