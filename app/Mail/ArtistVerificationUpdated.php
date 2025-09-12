<?php

namespace App\Mail;

use App\Models\Artist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistVerificationUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $artist;
    public $approved;
    public $reason;

    public function __construct(Artist $artist, bool $approved = true, ?string $reason = null)
    {
        $this->artist = $artist;
        $this->approved = $approved;
        $this->reason = $reason;
    }

    public function build()
    {
        if ($this->approved) {
            return $this->subject('Your Artist Application Approved')
                        ->view('emails.artist-approved')
                        ->with([
                          'artistName' => $this->artist->display_name,
                          'userName' => $this->artist->user->name ?? '',
                        ]);
        } else {
            return $this->subject('Your Artist Application Update')
                        ->view('emails.artist-rejected')
                        ->with([
                          'artistName' => $this->artist->display_name,
                          'reason' => $this->reason,
                        ]);
        }
    }
}
