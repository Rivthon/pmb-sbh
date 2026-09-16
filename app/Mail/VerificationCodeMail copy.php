<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verifyCode;
    public $user_name;
    public $user_id;
    public $email;
    public $prodi;

    /**
     * Create a new message instance.
     */
    public function __construct($verifyCode, string $user_name, string $user_id, string $email, string $prodi = '-')
    {
        $this->verifyCode = $verifyCode;
        $this->user_name = $user_name;
        $this->user_id = $user_id;
        $this->email = $email;
        $this->prodi = $prodi;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifikasi Akun PMB - STIKes Bogor Husada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'templates.mail.verify-code',
            with: [
                'verifyCode' => $this->verifyCode,
                'user_name'  => $this->user_name,
                'user_id'    => $this->user_id,
                'email'      => $this->email,
                'prodi'      => $this->prodi,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}