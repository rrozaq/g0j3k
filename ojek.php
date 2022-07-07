<?php
date_default_timezone_set('Asia/Jakarta');

echo color("white","                 AUTO REGIST\n" );
echo color("white","        Jauhkan Rasa Lapar Dari Perutmu\n" );
echo color("purple","                github: rrozaq\n\n");

echo color("green", "Pilihan ? ");
echo color("white", "
1. Daftar
2. Cek Voucher
");
echo color("green","\nJawaban : ");
$pilihan = trim(fgets(STDIN));

if($pilihan == 1) {
    echo color("green","Masukan nomor (62xxx) : ");
    $nomor = trim(fgets(STDIN));
    echo color("green", "MAU SET NAMA (y/n) ? ");
    $autonama = trim(fgets(STDIN));
    if($autonama == 'y' || $autonama == 'Y') {
        echo color("yellow","Masukkan Nama Depan & Belakang : ");
        $nama = trim(fgets(STDIN));
    } else if ($autonama == 'n' || $autonama == 'N') {
        $nama = nama();
    } else {
        echo color ('red','Pilihan Tidak Dikenali ');
        die();
    }
    echo color("grey", "Nama : " . $nama . "\n");

    ulang:
    $email = str_replace(" ", "", $nama) . mt_rand(1, 999);
    $data = array(
        "email" => $email . "@outlook.com",
        "name"  => $nama,
        "phone" => $nomor
    );
    $register = request("/daftar", $data);
    $jsonDecodeRegister = json_decode($register[0]);
    
    if(isset($jsonDecodeRegister->otp_token)){
        otp:
        echo color("grey","Kode verifikasi sudah di kirim!")."\n";
        echo color("white","Masukan OTP : ");
        $otp = trim(fgets(STDIN));
        $data1 = array(
            "otp"        => $otp,
            "otp_token"  => $jsonDecodeRegister->otp_token
        );
        $verif = request("/verify-otp-daftar", $data1);
        $jsonDecodeVerif = json_decode($verif[0]);

        if(isset($jsonDecodeVerif->access_token)){
            echo color("green","Berhasil mendaftar\n");

            request("/reedem-vouc", array('token' => $jsonDecodeVerif->access_token));
            echo "\n".color("purple","Tunggu! LAGI REDEEM VOUCHER");
            for($i = 0; $i < 3; $i ++) {
                echo ".";
                sleep(1);
            }
            
            echo "\n".color("green","\nCek Voucher? y/n : ");
            $pilihan = trim(fgets(STDIN));
            if($pilihan == "y" || $pilihan == "Y") {
                $dataRefreshToken = array(
                    "refresh_token" => $jsonDecodeVerif->refresh_token
                );
                $refreshVerif = request("/refresh-token", $dataRefreshToken);
                $jsonDecoderefreshVerif = json_decode($refreshVerif[0]);
        
                if(isset($jsonDecoderefreshVerif->access_token)){
                    $newToken = $jsonDecoderefreshVerif->access_token;
        
                    echo "\n".color("purple","Bentar lagi cek vouchermu");
                    for($i = 0; $i < 3; $i ++) {
                        echo ".";
                        sleep(1);
                    }
                    
                    $detail_voucher = curl('https://api.gojekapi.com/gopoints/v3/wallet/vouchers?limit=10&page=1', null, [
                        'Content-Type: application/json',
                        'X-AppVersion: 3.46.2',
                        "X-UniqueId: ".time()."57".mt_rand(1000,9999),
                        'X-Location: id_ID',
                        'Authorization: Bearer '.$newToken
                    ]);
                    $vouchers = json_decode($detail_voucher[0]);
                    $total_voucher = $vouchers->voucher_stats->total_vouchers;
                    echo color("blue","\nKamu Punya " . $total_voucher . " Voucher GOJEK");
        
                    if($total_voucher == 0) {
                        die();
                    }
        
                    if($vouchers->success) {
                        foreach($vouchers->data as $voucher) {
                            echo "Voucher : " . $voucher->title . " | Kadaluarsa : " . $voucher->expiry_date . "\n";
                        }
                    }
                } else {
                    echo color("red", "Gagal..");
                }
            } else {
                    die();
            }

        } else {
            echo color("white","╭─────────────────────────────────╮ \n");
            echo color("white","│    Otp yang anda input salah    │ \n");
            echo color("white","│      Silahkan input kembali     │ \n");
            echo color("white","┌─────────────────────────────────╯ \n");
            goto otp;
    }

    } else {
        echo color("white","╭─────────────────────────────────╮ \n");
        echo color("white","│ NOMOR SUDAH TERDAFTAR/SALAH !!! │ \n");
        echo color("white","┌─────────────────────────────────╯ \n");
        echo "\n".color("nevy","└──> Mau Ulang Register (y/n): \n");
        $pilih = trim(fgets(STDIN));
        if($pilih == "y" || $pilih == "Y"){
                echo color("white","╭─────────────────────────────────╮ \n");
                echo color("white","│  PASTIKAN NOMOR BELUM TERDAFTAR │ \n");
                echo color("white","┌─────────────────────────────────╯ \n");
                goto ulang;
        } else {
            echo die();
        }
    }
} else if($pilihan == 2) {
    echo color("green","Masukan nomor hp (628xxx) : ");
    $loginNomor = trim(fgets(STDIN));
    $dataLogin = array(
        "phone" => $loginNomor
    );
    $login = request("/login", $dataLogin);
    $jsonDecodeLogin = json_decode($login[0]);
    
    if($jsonDecodeLogin->success == false) {
        echo color("red", $message->errors[0]->message);
        die();
    }

    if($jsonDecodeLogin->success) {
        echo color("green","Kode OTP sudah di kirim!")."\n";
        echo color("white","OTP : ");
        $otpLogin = trim(fgets(STDIN));

        $dataverifyLogin = array(
            "otp"        => $otpLogin,
            "otp_token"  => $jsonDecodeLogin->data->otp_token
        );
        $verifyLogin = request("/verify", $dataverifyLogin);
        $jsonDecodeVerifyLogin = json_decode($verifyLogin[0]);


        if(isset($jsonDecodeVerifyLogin->access_token)){
            echo "\n".color("blue","Bentar lagi cek vouchermu");
            for($i = 0; $i < 3; $i ++) {
                echo ".";
                sleep(1);
            }
            
            $detail_voucher = curl('https://api.gojekapi.com/gopoints/v3/wallet/vouchers?limit=10&page=1', null, [
                'Content-Type: application/json',
                'X-AppVersion: 3.46.2',
                "X-UniqueId: ".time()."57".mt_rand(1000,9999),
                'X-Location: id_ID',
                'Authorization: Bearer '. $jsonDecodeVerifyLogin->access_token
            ]);
            $vouchers = json_decode($detail_voucher[0]);
            $total_voucher = $vouchers->voucher_stats->total_vouchers;
            echo color("blue","\nKamu Punya " . $total_voucher . " Voucher GOJEK");

            if($total_voucher == 0) {
                die();
            }

            if($vouchers->success) {
                foreach($vouchers->data as $voucher) {
                    echo "Voucher : " . $voucher->title . " | Kadaluarsa : " . $voucher->expiry_date . "\n";
                }
            }
        } else {
            echo "otp salah";
        }
    }
} else {
    die();
}

function curl($url, $fields = null, $headers = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($fields !== null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    }
    if ($headers !== null) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $result   = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        $result,
        $httpcode
    );
}

function request($url, $data) {
    $data["uniqueid"] = '';

    $curl = curl_init("http://68.183.186.230:8181/rrozaq" . $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return array($response . PHP_EOL);
}


function color($color = "default", $text = null)
{
    $arrayColor = array(
        'grey'      => '1;30',
        'red'       => '1;31',
        'green'     => '1;32',
        'yellow'    => '1;33',
        'blue'      => '1;34',
        'purple'    => '1;35',
        'nevy'      => '1;36',
        'white'     => '1;0',
        'default'   => '1;0',
    );  
    return "\033[".$arrayColor[$color]."m".$text."\033[0m";
}

function nama()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://ninjaname.horseridersupply.com/indonesian_name.php");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $ex = curl_exec($ch);
    // $rand = json_decode($rnd_get, true);
    preg_match_all('~(&bull; (.*?)<br/>&bull; )~', $ex, $name);
    return $name[2][mt_rand(0, 14) ];
}