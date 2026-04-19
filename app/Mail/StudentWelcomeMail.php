<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Student $student)
    {
    }

    public function build(): self
    {
        return $this->subject('مرحبًا بك في أكاديمية ليدرز')
            ->view('emails.student_welcome');
    }
}
