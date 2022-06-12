<?php

declare(strict_types=1);

namespace Api\V1\Controller\Progress;

use \Api\Core\Helper\ErrorHelper as ErrorHelper;
use \Carbon\Carbon as Carbon;

class Status
{
    private array $requiredParams = ['course_duration', 'course_progress', 'date_start', 'date_finish'];
    private array $allowedMethods = ['GET'];

    public function __construct()
    {

        $this->checkRequestMethod();

        $request = (array) json_decode(file_get_contents("php://input"), true);

        // Check for invalid requests
        $this->validateRequest($request);

        //get status
        $this->getStatus($request);
    }

    /**
     * Sends JSON encoded response
     *
     * @param array $request: The request json decoded body
     * 
     * @return void
     */

    private function getStatus(array $request): void
    {
        $courseDuration = (int) $request['course_duration']; // int| seconds
        $courseProgress = (int) $request['course_progress']; // int| %

        $dateStart = new Carbon($request['date_start']);
        $dateStart->timezone('Europe/Sofia');

        $dateFinish = new Carbon($request['date_finish']);
        $dateFinish->timezone('Europe/Sofia');

        $courseTimeframe = $dateStart->diffInSeconds($dateFinish); // in seconds

        // how man seconds should the student study each day to complete the course.
        $courseStudentPase = $this->getNeededDailyTime($courseTimeframe, $courseDuration);

        $idealProgress = $this->getProgressExpectation($dateStart, $courseTimeframe, $courseStudentPase);

        $progressStatus = $this->getProgressStatus($courseProgress, $idealProgress, $dateFinish);

        $response = [
            "progress_status" => $progressStatus,
            "expected_progress" => $idealProgress,
            "needed_daily_learning_time" => $courseStudentPase
        ];

        http_response_code(200);
        echo json_encode($response);
        exit;
    }

    /**
     * Calculates the "progress_status" for the responce
     *
     * @param int $courseProgress: Request "course_progress" variable
     * @param int $idealProgress: The ideal progress calculated by $this->getProgressExpectation()
     * @param Carbon $dateFinish: Carbon instance with the request "date_finish" variable.
     * 
     * @return string
     */

    private function getProgressStatus(int $courseProgress, int $idealProgress, Carbon $dateFinish): string
    {
        $dateTimeCurrent = Carbon::now('Europe/Sofia');
        $isCourseFinished = $dateTimeCurrent->greaterThan($dateFinish);

        if ($isCourseFinished && $courseProgress < 100) return 'overdue';

        if ($courseProgress >= $idealProgress) return 'on track';

        return 'not on track';
    }

    /**
     * Calculates the "expected_progress" for the responce
     *
     * @param Carbon $dateStart: Carbon instance with the request "date_start" variable.
     * @param int $courseTimeframe: The difference between the start and end date in seconds
     * 
     * @return int
     */

    private function getProgressExpectation(Carbon $dateStart, int $courseTimeframe): int
    {
        $dateTimeCurrent = Carbon::now('Europe/Sofia');
        $timePassed = $dateTimeCurrent->diffInSeconds($dateStart); // get time passed in seconds

        $idealProgress = $timePassed / $courseTimeframe;
        $idealProgress = ceil($idealProgress * 100);
        $idealProgress = (int) min($idealProgress, 100);

        return $idealProgress;
    }

    /**
     * Calculates the "needed_daily_learning_time" for the responce
     *
     * @param int $courseTimeframe: The difference between the start and end date in seconds
     * @param int $courseDuration: The difference between the start and end date in seconds
     * 
     * @return int
     */

    private function getNeededDailyTime(int $courseTimeframe, int $courseDuration): int
    {
        if (!(int) $courseDuration > 0) {
            http_response_code(422);
            echo json_encode(["message" => "The Course duration cannot be <= 0!"]);
            exit;
        }

        return (int) ceil(($courseDuration / $courseTimeframe) * 60 * 60 * 24); //seconds to study per day to complete the course
    }

    /**
     * Checks id request method is allowed
     * 
     * @return void
     */

    private function checkRequestMethod(): void
    {

        if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods)) {

            http_response_code(405);
            header("Allow: " . implode(',', $this->allowedMethods));
            exit;
        }
    }


    /**
     * Checks id request method is allowed
     * 
     * @param array $request: Validates request data
     * 
     * @return void
     */

    private function validateRequest(array $request): void
    {

        if (!empty(array_diff($this->requiredParams, array_keys($request)))) {
            http_response_code(422);
            echo json_encode(["message" => "Missing request parameters!"]);
            exit;
        }

        // validate input
        foreach ($request as $key => $value) {

            $errors[] = match ($key) {
                'course_duration'   => ErrorHelper::validateInput($key, $value, 'int'),
                'course_progress'   => ErrorHelper::validateInput($key, $value, 'int'),
                'date_start'        => ErrorHelper::validateInput($key, $value, 'date_RFC3339'),
                'date_finish'       => ErrorHelper::validateInput($key, $value, 'date_RFC3339'),
                default => 'Unexpected input ' . $key . '!',
            };
        }

        $errors = array_filter($errors); //remove nulls
        $errors = array_values($errors);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors]);
            exit;
        }
    }
}
