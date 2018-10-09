Feature:
  In order to move data from source to target database efficiently
  As a user
  I want to be able to do it in chunks

  Scenario Outline: One table - divide into chunks
    Given there is a source database
    And there is a table posts with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | title | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table posts contains following data:
      | id | title   | desc   |
      | 1  | title 1 | desc 1 |
      | 2  | title 2 | desc 2 |
      | 3  | title 3 | desc 3 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      posts:
    """
    When I run "run" command with input:
      | --chunk-size | <chunkSize> |
      | --file       | test.yaml   |
      | --dont-wait  | true        |
    Then I should see "<chunks> chunks have been added to queue" in command's output
    And the command should exit with code 0
    And published tasks counter should equal "<chunks>"
    And processed tasks counter should equal 0
    Examples:
      | chunkSize | chunks |
      | 100       | 1      |
      | 3         | 1      |
      | 2         | 2      |
      | 1         | 3      |

  Scenario Outline: Two tables divide into chunks
    Given there is a source database
    And there is a table posts with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | title | string  | 64     |         |
      | desc  | string  | 128    |         |
    And the table posts contains following data:
      | id | title   | desc   |
      | 1  | title 1 | desc 1 |
      | 2  | title 2 | desc 2 |
      | 3  | title 3 | desc 3 |
      | 4  | title 4 | desc 4 |
    And there is a table other with following columns:
      | name  | type    | length | index   |
      | id    | integer |        | primary |
      | other | string  | 64     |         |
    And the table other contains following data:
      | id | other   |
      | 1  | other 1 |
      | 2  | other 2 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      posts:
      other:
    """
    When I run "run" command with input:
      | --chunk-size | <chunkSize> |
      | --file       | test.yaml   |
      | --dont-wait  | true        |
    Then I should see "<chunks> chunks have been added to queue" in command's output
    And published tasks counter should equal "<chunks>"
    And processed tasks counter should equal 0
    Examples:
      | chunkSize | chunks |
      | 100       | 2      |
      | 4         | 2      |
      | 3         | 3      |
      | 2         | 3      |
      | 1         | 6      |

  Scenario: Table without primary and unique key should not be divided to chunks even if it is larger than chunk size
    Given there is a source database
    And there is a table posts with following columns:
      | name  | type    | length |
      | id    | integer |        |
      | title | string  | 64     |
      | desc  | string  | 128    |
    And the table posts contains following data:
      | id | title   | desc   |
      | 1  | title 1 | desc 1 |
      | 2  | title 2 | desc 2 |
      | 3  | title 3 | desc 3 |
      | 4  | title 4 | desc 4 |
      | 5  | title 5 | desc 5 |
    And there is an empty target database
    And the task queue is empty
    And the config test.yaml contains:
    """
    tables:
      posts:
    """
    When I run "run" command with input:
      | --chunk-size | 2         |
      | --file       | test.yaml |
      | --dont-wait  | true      |
    Then I should see "1 chunks have been added to queue" in command's output
    And published tasks counter should equal 1
    And processed tasks counter should equal 0