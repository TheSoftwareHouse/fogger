Feature:
  In order to make the resulting database smaller
  As a user
  I want to be able to subset selected tables

  Background:
    Given there is a source database
    And there is a table posts with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | title | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table posts contains following data:
      | id | title   | desc   |
      | 4  | title 4 | desc 4 |
      | 3  | title 3 | desc 3 |
      | 1  | title 1 | desc 1 |
      | 2  | title 2 | desc 2 |
      | 5  | title 5 | desc 5 |
      | 6  | title 6 | desc 6 |
    And there is an empty target database
    And the task queue is empty

  Scenario: We want only the last records (tail strategy) from the table
    Given the config test.yaml contains:
    """
    tables:
      posts:
        subsetStrategy: tail
        subsetOptions: { length: 3 }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    Then I should see "1 chunks have been added to queue" in command's output
    And the command should exit with code 0
    And published tasks counter should equal 1
    And processed tasks counter should equal 0
    When worker processes 1 task
    Then processed tasks counter should equal 1
    And the table posts in target database should have 3 row
    And the table posts in target database should contain rows:
      | id | title   |
      | 4  | title 4 |
      | 5  | title 5 |
      | 6  | title 6 |

  Scenario: We want only the last records (tail strategy) from the table (multiple chunks)
    Given the config test.yaml contains:
    """
    tables:
      posts:
        subsetStrategy: tail
        subsetOptions: { length: 3 }
    """
    When I run "run" command with input:
      | --chunk-size | 2         |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    Then I should see "2 chunks have been added to queue" in command's output
    And the command should exit with code 0
    And published tasks counter should equal 2
    And processed tasks counter should equal 0
    When worker processes 2 tasks
    Then processed tasks counter should equal 2
    And the table posts in target database should have 3 row
    And the table posts in target database should contain rows:
      | id | title   |
      | 4  | title 4 |
      | 5  | title 5 |
      | 6  | title 6 |
