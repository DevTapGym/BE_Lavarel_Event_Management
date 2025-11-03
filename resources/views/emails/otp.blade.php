@component('mail::message')
# <span style="color:#1E90FF;">Xác thực tài khoản</span>

<p style="font-size:15px; color:#333;">
    Bạn nhận được email này vì đã thực hiện đăng ký tài khoản hoặc yêu cầu xác thực trên hệ thống quản lý sự kiện 
    <strong style="color:#1E90FF;">HUIT</strong>.<br>
    Vui lòng sử dụng mã OTP bên dưới để hoàn tất quá trình xác thực tài khoản của bạn.
</p>

@component('mail::panel')
<p style="font-size:20px; color:#1E90FF; text-align:center; font-weight:bold;">
    {{ $otp }}
</p>
@endcomponent

<p style="font-size:14px; color:#333;">
    Mã này sẽ hết hạn sau <strong>3 phút</strong>.<br>
    Nếu bạn không yêu cầu, vui lòng <strong>bỏ qua email này</strong>.
</p>

<hr style="border:none; border-top:1px solid #E0E0E0; margin:20px 0;">

<p style="color:#1E90FF; font-weight:bold;">
    Cảm ơn,<br>
    {{ config('app.name') }}
</p>
@endcomponent
