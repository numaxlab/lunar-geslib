<?php

namespace NumaxLab\Lunar\Geslib\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibFileInterResource; // Added for Filament URL

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
        // Check if Filament classes and GeslibFileInterResource are available to generate URL
        if (class_exists(GeslibFileInterResource::class) && method_exists(GeslibFileInterResource::class, 'getUrl')) {
            try {
                // Link to the GeslibFileInterResource list page, filtered by status 'error'
                $fileImportLogUrl = GeslibFileInterResource::getUrl('index', ['tableFilters[status][value]' => 'error', 'tableSearchQuery' => $this->file->name]);
            } catch (\Exception $e) {
                // In case URL generation fails for any reason (e.g., panel not registered yet during certain operations)
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
            $mailMessage->action('View File Import Log', $fileImportLogUrl);
        } else {
            $mailMessage->line('Please check the Geslib File Import Logs in your Filament admin panel for more details.');
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
