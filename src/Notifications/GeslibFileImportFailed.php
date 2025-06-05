<?php

namespace NumaxLab\LunarGeslib\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NumaxLab\LunarGeslib\Models\GeslibInterFile;

class GeslibFileImportFailed extends Notification implements ShouldQueue
{
    use Queueable;

    protected GeslibInterFile $file;
    protected string $errorMessage;

    /**
     * Create a new notification instance.
     *
     * @param GeslibInterFile $file
     * @param string $errorMessage
     */
    public function __construct(GeslibInterFile $file, string $errorMessage)
    {
        $this->file = $file;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $fileImportLogUrl = null;
        if (class_exists(\NumaxLab\LunarGeslib\Admin\Http\Livewire\Components\GeslibFileImportLog::class) && config('lunar.admin.route_prefix')) {
             // Try to generate the URL if the route might exist
            try {
                $fileImportLogUrl = route('adminhub.geslib.file-import-log', ['q_filename' => $this->file->name]);
            } catch (\Exception $e) {
                // Route might not be defined yet or other issue
                $fileImportLogUrl = null;
            }
        }


        $mailMessage = (new MailMessage)
            ->subject('Geslib File Import Failed: ' . $this->file->name)
            ->greeting('Geslib File Import Failure')
            ->line('A file import from Geslib has failed.')
            ->line('Filename: ' . $this->file->name)
            ->line('Timestamp: ' . now()->toDateTimeString())
            ->line('Error Message: ' . $this->errorMessage);

        if ($fileImportLogUrl) {
            $mailMessage->action('View Import Logs', $fileImportLogUrl);
        } else {
            $mailMessage->line('Please check the Geslib File Import Logs in your admin panel for more details.');
        }

        $mailMessage->line('Thank you for using our application!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'file_id' => $this->file->id,
            'filename' => $this->file->name,
            'error_message' => $this->errorMessage,
        ];
    }
}
