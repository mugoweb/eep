# Modules - user
> The user module provides methods to retrieve user information not accessible through the content object or content node modules.

- [editlog](#editlog)
- [visit](#visit)
- [addsubtreenotification](#addsubtreenotification)
- [removesubtreenotification](#removesubtreenotification)
- [listsubtreenotifications](#listsubtreenotifications)

## editlog
Dump list of users who have edited content in the last N months (defaults to 3) and who have edited more than 1 piece of content.
```sh
$ eep user editlog [<number of months>]
```

## visit
Returns user visit information e.g. last login, login count
```sh
$ eep user visit <user_id>
```

## addsubtreenotification
Adds a subtree notification
```sh
eep user addsubtreenotification <user_id> <node_id>
```

## removesubtreenotification
Removes a subtree notification
```sh
eep user removesubtreenotification <user_id> <node_id>
```

## listsubtreenotifications
Lists user's subtree notifications
```sh
eep user listsubtreenotifications <user_id>
```

