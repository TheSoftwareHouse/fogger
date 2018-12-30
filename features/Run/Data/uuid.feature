Feature:
  In order to copy masked data properly
  As a user
  I want to handle uuids in both mysql and postgres databases

  Scenario: Copying uuids
    Given there is a source database
    And there is a table test with following columns:
      | name | type    | length | index   |
      | id   | uuid    |        | primary |
      | text | string  | 64     |         |
    And the table test contains following data:
      | id | text                     |
      | 53995185-7308-44bc-b7f5-39eff96c7c32  | a text |
      | 2e0413363-29c9-4541-920e-dccb8a9f88f0 | another text |
      | 40f6a21a-c45a-4d8b-9b42-e13f64782b19  | yet another text                  |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      test: ~
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    And I run "finish" command with input:
      | --file | test.yaml |
    Then the command should exit with code 0
    And the table test in target database should have 3 rows
    And the table test in target database should contain rows:
      | id | text                     |
      | 53995185-7308-44bc-b7f5-39eff96c7c32  | a text |
      | 2e0413363-29c9-4541-920e-dccb8a9f88f0 | another text |
      | 40f6a21a-c45a-4d8b-9b42-e13f64782b19  | yet another text                  |
