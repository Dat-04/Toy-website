<?php
// Cấu hình VNPay
return [
    'vnp_TmnCode' => "TOYSHOP01", // Mã website tại VNPAY 
    'vnp_HashSecret' => "OQZJTSJRNQWJZXMFYUUASKJBPPKXPNWZ", // Chuỗi bí mật
    'vnp_Url' => "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html",
    'vnp_ReturnUrl' => "http://localhost/toy/vnpay_return.php",
    'vnp_ApiUrl' => "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html",
    'vnp_Version' => "2.1.0",
    'vnp_Command' => "pay",
    'vnp_CurrCode' => 'VND'
]; 