Feature:
  In order to copy masked data properly
  As a user
  I want to get correctly encoded data in target database

  Scenario: We want to subset the table that is referenced by other table (Not Null column)
    Given there is a source database
    And there is a table test with following columns:
      | name | type    | length | index   |
      | id   | integer |        | primary |
      | text | string  | 64     |         |
    And the table test contains following data:
      | id | text                     |
      | 1  | zażółć gęślą jaźń        |
      | 2  | المملكة العربية السعودية |
      | 3  | 中华人民共和国                  |
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
      | 1  | zażółć gęślą jaźń        |
      | 2  | المملكة العربية السعودية |
      | 3  | 中华人民共和国                  |
