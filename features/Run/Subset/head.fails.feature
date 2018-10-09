Feature:
  In order to make the resulting database smaller
  As a user
  I want to be able to subset selected tables

  Background:
    Given there is a source database
    And there is a table posts with following columns:
      | name  | type    | length | index |
      | id    | integer |        |       |
      | title | string  | 64     |       |
      | desc  | string  | 128    |       |
    And the table posts contains following data:
      | id | title   | desc   |
      | 1  | title 1 | desc 1 |
    And there is an empty target database
    And the task queue is empty

  Scenario: We cannot apply head subset to table without primary key
    Given the config test.yaml contains:
    """
    tables:
      posts:
        subsetStrategy: head
        subsetOptions: { length: 2 }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    Then I should see "Error! Strategy require the table to have a unique sortBy column" in command's output
    And the command should exit with code "-1"

  Scenario: Startegy requires option length to be provided
    Given the config test.yaml contains:
    """
    tables:
      posts:
        subsetStrategy: head
        subsetOptions: { }
    """
    When I run "run" command with input:
      | --chunk-size | 1000      |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    Then I should see 'requires option "length" to be set' in command's output
    And the command should exit with code "-1"
