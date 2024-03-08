<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendDocuments extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $solicitationId;

    /**
     * Create a new message instance.
     *
     * @param  string  $name
     * @param  int  $solicitationId
     * @return void
     */
    public function __construct($name, $solicitationId)
    {
        $this->name = $name;
        $this->solicitationId = $solicitationId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Solicitação de Documentação para Cliente')
            ->view('emails.send-documents')
            ->with([
                'name' => $this->name,
                'solicitationId' => $this->solicitationId,
            ]);
    }
}
