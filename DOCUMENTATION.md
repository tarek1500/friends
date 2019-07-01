# Documentation

1. First of all you will have to publish the package resources:
```bash
php artisan vendor:publish --provider=TarekIM\Friends\FriendServiceProvider
```
>Available resources to publish are **factories**, **seeds** and **migrations**. Use whatever tag to publish specific resource.

2. Run database migrations:
```bash
php artisan migrate
```

3. Then use the **Friendable** trait in your model:
```php
use TarekIM\Friends\Friendable;

class User extends Authenticatable
{
	use Friendable;
	...
}
```
Now you are ready to use the friendship methods on the **User** model.

## List of available methods
| Method | Description |
| --- | --- |
| [`friendships`](#friendships) | Get all friendships related to the current model. |
| [`friendship`](#friendship) | Get specific friendship related to the current model. |
| [`sendFriendRequest`](#sendFriendRequest) | Send a friend request to another model. |
| [`acceptFriendRequest`](#acceptFriendRequest) | Accept a friend request sent by another model. |
| [`denyFriendRequest`](#denyFriendRequest) | Deny a friend request sent by another model. |
| [`blockFriend`](#blockFriend) | Block a friend for the current model. |
| [`unblockFriend`](#unblockFriend) | Remove an old blocked friend for the current model. |
| [`cancelFriendRequest`](#cancelFriendRequest) | Cancel a friend request sent by the current model. |
| [`unfriend`](#unfriend) | Remove a friend by the current model. |
| [`getPending`](#getPending) | Get all pending requests sent to the current model. |
| [`getFriends`](#getFriends) | Get all friends for the current model. |
| [`getBlocked`](#getBlocked) | Get all blocked friendships by the current model. |

## Deeper Overview
### `friendships`
```php
function friendships() : EloquentBuilder
```
* Returns an instance of Eloquent query builder.

### `friendship`
```php
function friendship(Model $sender, $receiver = false, bool $self = false) : EloquentBuilder
```
* Takes 1 required argument and 2 optional arguments:
  - **First argument** is the receiver, or the sender if the second argument is a model.
  - **Second argument** is the self flag, or the receiver if this argument is a model.
  - **Third argument** is the self flag to indicate if it is one way relation only from the sender.
* Returns an instance of Eloquent query builder.

### `sendFriendRequest`
```php
function sendFriendRequest(Model $model) : Friend|null
```
* Takes 1 argument which is the receiver.
* Returns an instance of Friend model, or null if there is already a friendship.

### `acceptFriendRequest`
```php
function acceptFriendRequest(Model $model) : Friend|null
```
* Takes 1 argument which is the sender.
* Returns an instance of Friend model, or null if there is no friendship or a friendship with a not pending status.

### `denyFriendRequest`
```php
function denyFriendRequest(Model $model) : bool
```
* Takes 1 argument which is the sender.
* Returns a boolean value. True if there is a friendship with a pending status, otherwise false.

### `blockFriend`
```php
function blockFriend(Model $model) : Friend|null
```
* Takes 1 argument which is the receiver.
* Returns an instance of Friend model, or null if there is friendship with a block status.

### `unblockFriend`
```php
function unblockFriend(Model $model) : bool
```
* Takes 1 argument which is the receiver.
* Returns a boolean value. True if there is a friendship with a block status, Otherwise false.

### `cancelFriendRequest`
```php
function cancelFriendRequest(Model $model) : bool
```
* Takes 1 argument which is the receiver.
* Returns a boolean value. True if there is a friendship with a pending status, otherwise false.

### `unfriend`
```php
function unfriend(Model $model) : bool
```
* Takes 1 argument which is the receiver.
* Returns a boolean value. True if there is a friendship with a accept status, otherwise false.

### `getPending`
```php
function getPending() : EloquentBuilder
```
* Returns an instance of Eloquent query builder.

### `getFriends`
```php
function getFriends() : EloquentBuilder
```
* Returns an instance of Eloquent query builder.

### `getBlocked`
```php
function getBlocked() : EloquentBuilder
```
* Returns an instance of Eloquent query builder.

### Dynamic Retrieving
You can call some methods as dynamic attributes calling:
```php
$friendships = $user->friendships;

$pending = $user->getPending;
$friends = $user->getFriends;
$blocked = $user->getBlocked;

// or you can use a shortcut
$pending = $user->pending;
$friends = $user->friends;
$blocked = $user->blocked;
```
But make sure your model doesn't have any of the following attribute names:

    friendships, getPending, pending, getFriends, friends, getBlocked or blocked

Or you can disable this feature by assigning the Dynamic Attributes flag in any of your service provider boot method:
```php
public function boot()
{
	Friend::$enableDynamicAttributes = false;
}
```

## Relations
You can use the **sender** and **receiver** relations on Friend model:
```php
$sender = Friend::find(1)->sender;
$receiver = Friend::find(1)->receiver;
```

## List of available scopes
| Scope | Description |
| --- | --- |
| `whereSender` | Add where statement on the sender. |
| `orWhereSender` | Add orWhere statement on the sender. |
| `whereReceiver` | Add where statement on the receiver. |
| `orWhereReceiver` | Add orWhere statement on the receiver. |

## List of events
| Event | Description |
| --- | --- |
| `FriendRequest` | Fires when a friend request sent to another model. |
| `AcceptFriend` | Fires when a model accepts another model's friend request. |
| `DenyFriend` | Fires when a model denies another model's friend request. |
| `BlockFriend` | Fires when a model blocks another model. |
| `UnblockFriend` | Fires when a model unblocks another model. |
| `CancelRequest` | Fires when a model cancels his friend request to another model. |
| `Unfriend` | Fires when a model removes a friend. |
>All of the previous events extend the parent event `FriendEvent`.