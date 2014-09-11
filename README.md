# Clerk

Clerk is a command line tool for posting timesheets to SugarInternal. It allows to import timesheets from a text file and automatically recognizes timesheet activity and task.

## Installation

Clone the project repository and install it with composer:

```
git clone git@github.com:morozov/clerk.git
cd clerk
composer install
```

## Configuration

Copy the example config `config.xml.dist` to `config.xml`, enter connection parameters and adjust timesheet classifier.

Every entry of `Clerk\Classifier\Task` represents your task in SugarInternal. Define as many of them as you have. The first argument is the task name (needed only for preview), the second is the ID from SugarInternal:

```xml
<service id="task.bugfixing" class="Clerk\Classifier\Task">
    <argument>Bugfixing</argument>
    <argument>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</argument>
</service>
```

Every entry of `Clerk\Classifier\Activity` represents your activity. Define as many of them as you need. The first argument is the activity name (needed for preview _and import_), the second is the task ID defined above:

```xml
<service id="activity.project-support" class="Clerk\Classifier\Activity">
    <argument>Project Support</argument>
    <argument type="service" id="task.bugfixing"/>
</service>
```

The relationship between tasks and activities is one-to-many. Thus, if you need the same activity for different tasks, create one activity for each task.

## Usage

Run the script from command line, enter timesheet data and press Ctrl+D:
```
php clerk.php import

Enter timesheet data and press Ctrl+D:
Sep 10
Task 1 - 2.5
Task 2 - 5.5

Sep 11
Task 1 - 3.5
Task 2 - 2
Interview with Vassily Pupkin - 1
Task 3 - 2
```

Once the input is parsed, you'll see the preview of what is about to be sent:
```
Date: 10 Sep, Wednesday
Subject: Task 1
Spent: 2.5
Activity: Project Support
Task: Bugfixing

Date: 10 Sep, Wednesday
Subject: Task 2
Spent: 5.5
Activity: Project Support
Task: Bugfixing

Date: 11 Sep, Thursday
Subject: Task 1
Spent: 3.5
Activity: Project Support
Task: Bugfixing

Date: 11 Sep, Thursday
Subject: Task 2
Spent: 2
Activity: Project Support
Task: Bugfixing

Date: 11 Sep, Thursday
Subject: Interview with Vassily Pupkin
Spent: 1
Activity: Interview
Task: General Activities

Date: 11 Sep, Thursday
Subject: Task 3
Spent: 2
Activity: Project Support
Task: Bugfixing

Continue with this action? [Y/n]
```

Check if tasks and activities are properly recognized and press Enter to proceed. The tasks will be imported:
```
Importing BR-1995                                         
1/4 [=======>--------------------]  25% (2 mins remaining)
```
