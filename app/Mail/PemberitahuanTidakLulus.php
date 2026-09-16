<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PemberitahuanTidakLulus extends Mailable
{
    use Queueable, SerializesModels;

    public $mahasiswa;
    public $periode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $mahasiswa, Periode $periode)
    {
        $this->mahasiswa = $mahasiswa;
        $this->periode = $periode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pemberitahuan Hasil Seleksi PMB STIKes Bogor Husada')
                    ->view('templates.mail.tidak_lulus_pmb');
    }
}

