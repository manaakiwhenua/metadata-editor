<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Metadata_provenance
{
    public const TITLE = "Provenance";
    public const GENERATED_FROM_TEXT = "This document was generated from the metadata record in the Metadata Editor.";
    private const ISO_DATE_FORMAT = DateTime::ATOM;
    private const TIMEZONE = 'UTC';
    private const NEW_ZEALAND_TIMEZONE = 'Pacific/Auckland';

    /** @var string */
    public $user_name;
    /** @var string */
    public $generated_on_new_zealand_time;
    /** @var string */
    public $generated_on_utc;


    public function __construct($user_name = null)
    {
        $date = new DateTimeImmutable('now');
        $this->user_name = $user_name ?? '';
        $this->generated_on_new_zealand_time = $this->format_date($date, self::ISO_DATE_FORMAT, self::NEW_ZEALAND_TIMEZONE);
        $this->generated_on_utc = $this->format_date($date, self::ISO_DATE_FORMAT, self::TIMEZONE);
    }

    private function format_date(DateTimeImmutable $date, string $format, string $timezone): string
    {
        return $date
        ->setTimezone(new DateTimeZone($timezone))
        ->format($format);
    }
}
