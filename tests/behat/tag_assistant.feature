@qbank @qbank_tagassistant
Feature: Question Bank Tag Assistant
  In order to tag questions consistently
  As a teacher
  I need to see and select established Question Bank tags as clickable chips in the question editor

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test Category  |

  @javascript
  Scenario: Teacher sees tag chips in question editor and clicks to add a tag
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    When I press "Create a new question ..."
    And I set the field "Multiple choice" to "1"
    And I press "Add"
    Then I should see "Frequent tags in this Question Bank:"
