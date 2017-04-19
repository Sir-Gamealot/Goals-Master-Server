# swagger-android-client

## Requirements

Building the API client library requires [Maven](https://maven.apache.org/) to be installed.

## Installation

To install the API client library to your local Maven repository, simply execute:

```shell
mvn install
```

To deploy it to a remote Maven repository instead, configure the settings of the repository and execute:

```shell
mvn deploy
```

Refer to the [official documentation](https://maven.apache.org/plugins/maven-deploy-plugin/usage.html) for more information.

### Maven users

Add this dependency to your project's POM:

```xml
<dependency>
    <groupId>io.swagger</groupId>
    <artifactId>swagger-android-client</artifactId>
    <version>1.0.0</version>
    <scope>compile</scope>
</dependency>
```

### Gradle users

Add this dependency to your project's build file:

```groovy
compile "io.swagger:swagger-android-client:1.0.0"
```

### Others

At first generate the JAR by executing:

    mvn package

Then manually install the following JARs:

* target/swagger-android-client-1.0.0.jar
* target/lib/*.jar

## Getting Started

Please follow the [installation](#installation) instruction and execute the following Java code:

```java

import io.swagger.client.api.DefaultApi;

public class DefaultApiExample {

    public static void main(String[] args) {
        DefaultApi apiInstance = new DefaultApi();
        GoalItem goalItem = new GoalItem(); // GoalItem | Goal item to add
        try {
            apiInstance.addGoal(goalItem);
        } catch (ApiException e) {
            System.err.println("Exception when calling DefaultApi#addGoal");
            e.printStackTrace();
        }
    }
}

```

## Documentation for API Endpoints

All URIs are relative to *https://virtserver.swaggerhub.com/Sir_Gamealot/GoalsMaster/1.0.0*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*DefaultApi* | [**addGoal**](docs/DefaultApi.md#addGoal) | **POST** /goals | adds an goal item
*DefaultApi* | [**addTask**](docs/DefaultApi.md#addTask) | **POST** /tasks | adds an task item
*DefaultApi* | [**getGoals**](docs/DefaultApi.md#getGoals) | **GET** /goals | gets Goals
*DefaultApi* | [**getTasks**](docs/DefaultApi.md#getTasks) | **GET** /tasks | gets Tasks


## Documentation for Models

 - [GoalItem](docs/GoalItem.md)
 - [TaskItem](docs/TaskItem.md)


## Documentation for Authorization

All endpoints do not require authorization.
Authentication schemes defined for the API:

## Recommendation

It's recommended to create an instance of `ApiClient` per thread in a multithreaded environment to avoid any potential issues.

## Author

office@goalsmaster.com

