<?php

namespace Mindigo\AcademicCalendar\Data;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use JsonSerializable;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;
use Mindigo\AcademicCalendar\Enums\CalendarEventSource;
use Mindigo\AcademicCalendar\Enums\CalendarEventStatus;

final readonly class CalendarEvent implements JsonSerializable
{
    /**
     * @param  list<string>  $actions
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public CalendarEventSource $source,
        public int|string $sourceId,
        public CalendarEventKind $kind,
        public CalendarEventStatus $status,
        public string $title,
        public CarbonImmutable $startsAt,
        public ?CarbonImmutable $endsAt,
        public string $timezone,
        public ?int $classroomId = null,
        public ?int $courseId = null,
        public ?int $lessonId = null,
        public ?int $ownerId = null,
        public ?string $url = null,
        public array $actions = ['view'],
        public array $metadata = [],
    ) {
        if ($endsAt && $endsAt->lessThanOrEqualTo($startsAt)) {
            throw new InvalidArgumentException('Calendar event end must be after its start.');
        }

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException('Calendar event timezone must be a valid IANA timezone.');
        }
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source->value,
            'source_id' => $this->sourceId,
            'kind' => $this->kind->value,
            'status' => $this->status->value,
            'title' => $this->title,
            'starts_at' => $this->startsAt->toIso8601String(),
            'ends_at' => $this->endsAt?->toIso8601String(),
            'timezone' => $this->timezone,
            'classroom_id' => $this->classroomId,
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
            'owner_id' => $this->ownerId,
            'url' => $this->url,
            'actions' => $this->actions,
            'metadata' => $this->metadata,
        ];
    }
}
