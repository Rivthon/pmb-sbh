<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verifyCode, $user_name, $user_id, $prodi, $password_plaintext;

    /**
     * Create a new message instance.
     */
    public function __construct(int $verifyCode, string $user_name, string $user_id, string $prodi, ?string $password_plaintext = null)
    {
        $this->verifyCode = $verifyCode;
        $this->user_name = $user_name;
        $this->user_id = $user_id;
        $this->prodi = $prodi;
        $this->password_plaintext = $password_plaintext;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Verifikasi Akun Anda - STIKes Bogor Husada')
            ->view('templates.mail.verify-code')
            ->with([
                'verifyCode' => $this->verifyCode,
                'user_name' => $this->user_name,
                'user_id' => $this->user_id,
                'prodi' => $this->prodi,
                'password_plaintext' => $this->password_plaintext,
            ]);
    }
}
