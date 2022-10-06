<?php

namespace App\Mail;

// use Illuminate\Bus\Queueable;
// use Illuminate\Mail\Mailable;
// use Illuminate\Queue\SerializesModels;

// class RegisterSuccess extends Mailable
// {
//     use Queueable, SerializesModels;

//     /**
//      * 郵件主旨
//      *
//      * @var string
//      */
//     public $subject;

//     /**
//      * 資料集
//      *
//      * @var \Illuminate\Support\Collection|array
//      */
//     protected $data;

//     /**
//      * Create a new message instance.
//      *
//      * @param string $subject 郵件主旨
//      * @param \Illuminate\Support\Collection|array $data 註冊成功的資料
//      * @return void
//      */
//     public function __construct(string $subject, $data)
//     {
//         $this->subject = $subject;
//         $this->data = ($data instanceof \Illuminate\Support\Collection) ? $data->toArray() : $data;
//     }

//     /**
//      * Build the message.
//      *
//      * @return $this
//      */
//     public function build()
//     {
//         $subject = $this->subject;
//         $data = $this->data;
//         return $this->subject($subject)->markdown('vendor.mail.mail', compact('subject', 'data'));
//     }

//     /**
//      * The number of seconds to wait before retrying the job.
//      *
//      * @var int
//      */
//     public $retryAfter = 3;
// }
