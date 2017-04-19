# DefaultApi

All URIs are relative to *https://virtserver.swaggerhub.com/Sir_Gamealot/GoalsMaster/1.0.0*

Method | HTTP request | Description
------------- | ------------- | -------------
[**addGoal**](DefaultApi.md#addGoal) | **POST** /goals | adds an goal item
[**addTask**](DefaultApi.md#addTask) | **POST** /tasks | adds an task item
[**getGoals**](DefaultApi.md#getGoals) | **GET** /goals | gets Goals
[**getTasks**](DefaultApi.md#getTasks) | **GET** /tasks | gets Tasks


<a name="addGoal"></a>
# **addGoal**
> addGoal(goalItem)

adds an goal item

Adds an goal to the user

### Example
```java
// Import classes:
//import io.swagger.client.api.DefaultApi;

DefaultApi apiInstance = new DefaultApi();
GoalItem goalItem = new GoalItem(); // GoalItem | Goal item to add
try {
    apiInstance.addGoal(goalItem);
} catch (ApiException e) {
    System.err.println("Exception when calling DefaultApi#addGoal");
    e.printStackTrace();
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **goalItem** | [**GoalItem**](GoalItem.md)| Goal item to add | [optional]

### Return type

null (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

<a name="addTask"></a>
# **addTask**
> addTask(taskItem)

adds an task item

Adds an task to the user

### Example
```java
// Import classes:
//import io.swagger.client.api.DefaultApi;

DefaultApi apiInstance = new DefaultApi();
TaskItem taskItem = new TaskItem(); // TaskItem | Task item to add
try {
    apiInstance.addTask(taskItem);
} catch (ApiException e) {
    System.err.println("Exception when calling DefaultApi#addTask");
    e.printStackTrace();
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **taskItem** | [**TaskItem**](TaskItem.md)| Task item to add | [optional]

### Return type

null (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

<a name="getGoals"></a>
# **getGoals**
> List&lt;GoalItem&gt; getGoals(userid)

gets Goals

By passing in the appropriate options, you can search for available inventory in the system 

### Example
```java
// Import classes:
//import io.swagger.client.api.DefaultApi;

DefaultApi apiInstance = new DefaultApi();
String userid = "userid_example"; // String | the user id
try {
    List<GoalItem> result = apiInstance.getGoals(userid);
    System.out.println(result);
} catch (ApiException e) {
    System.err.println("Exception when calling DefaultApi#getGoals");
    e.printStackTrace();
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **userid** | **String**| the user id | [optional]

### Return type

[**List&lt;GoalItem&gt;**](GoalItem.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

<a name="getTasks"></a>
# **getTasks**
> List&lt;TaskItem&gt; getTasks(userid)

gets Tasks

By passing in the appropriate options, you can search for available inventory in the system 

### Example
```java
// Import classes:
//import io.swagger.client.api.DefaultApi;

DefaultApi apiInstance = new DefaultApi();
String userid = "userid_example"; // String | the user id
try {
    List<TaskItem> result = apiInstance.getTasks(userid);
    System.out.println(result);
} catch (ApiException e) {
    System.err.println("Exception when calling DefaultApi#getTasks");
    e.printStackTrace();
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **userid** | **String**| the user id | [optional]

### Return type

[**List&lt;TaskItem&gt;**](TaskItem.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

