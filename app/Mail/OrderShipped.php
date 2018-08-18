<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;
    public $view = null;
    public $viewData = null;
    /**
     * Create a new message instance.
     *
     * @return void
     */

    /*
     * @var view, viewData, subject(Mail Title)
     * */
    public function __construct($view, $viewData, $subject = '')
    {
        //
        $this->view = $view;
        $this->viewData = $viewData;
        $this->subject = $subject ? $subject : config('app.email_subject') ;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address'=>config('app.email_address'), 'name'=>config('app.name')])->view($this->view)->subject($this->subject)->with($this->viewData);
    }
}
