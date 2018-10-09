Feature:
  In order to have the same constraint and indexes like on the source database
  As a user
  I want to be able to restore constraint and indexes

  Scenario: finish command should restore constraints and indexes on target database
    Given there is a source database
    And there is a table users with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | email | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table users contains following data:
      | id | email      | desc   |
      | 1  | ex1@tsh.io | desc 1 |
      | 2  | ex2@tsh.io | desc 2 |
      | 3  | ex3@tsh.io | desc 3 |
      | 4  | ex4@tsh.io | desc 4 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        columns:
          email: { maskStrategy: "faker"}
    """
    And I run "run" command with input:
      | --chunk-size | 2         |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 2 task
    When I run "finish" command with input:
      | --file       | test.yaml |
    Then I should see 'Data moved, constraints and indexes recreated.' in command's output

  Scenario: finish command should inform user that processing data is still going on
    Given there is a source database
    And there is a table users with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | email | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table users contains following data:
      | id | email      | desc   |
      | 1  | ex1@tsh.io | desc 1 |
      | 2  | ex2@tsh.io | desc 2 |
      | 3  | ex3@tsh.io | desc 3 |
      | 4  | ex4@tsh.io | desc 4 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        columns:
          email: { maskStrategy: "faker"}
    """
    And I run "run" command with input:
      | --chunk-size | 2         |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 1 task
    When I run "finish" command
    Then I should see 'We are still working on it, please try again later (1/2)' in command's output

  Scenario: finish command should inform user about errors which occurs when workers processed data
    Given there is a source database
    And there is a table users with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | email | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table users contains following data:
      | id | email      | desc   |
      | 1  | ex1@tsh.io | desc 1 |
      | 2  | ex2@tsh.io | desc 2 |
      | 3  | ex3@tsh.io | desc 3 |
      | 4  | ex4@tsh.io | desc 4 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      users:
        columns:
          email: { maskStrategy: "wrongMask"}
    """
    And I run "run" command with input:
      | --chunk-size | 2         |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    And worker processes 2 task
    When I run "finish" command
    Then I should see 'There has been an error' in command's output
    Then I should see 'Unknown mask "wrongMask".' in command's output