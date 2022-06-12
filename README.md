# api_get_course_progress_status

1) run composer update

[Sample data]

Endpoint: GET http://localhost/api/v1/progress/status


request body:

{
    "course_duration":"864000",
    "course_progress":"86",
    "date_start":"2022-06-05T00:00:00Z",
    "date_finish":"2022-06-14T00:00:00Z"
}


expected response:

{
    "progress_status": "on track",
    "expected_progress": 86,
    "needed_daily_learning_time": 96001
}
