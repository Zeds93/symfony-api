# Sungazer Symfony Challenge

This is a short coding challenge dedicated to understand your knowledge of Symfony and PHP.

The application is already set up, just run `composer install` to install all dependencies.

While developing, you can verify the correctness of your implementation using the provided tests.
Just run `./bin/phpunit` from the project's root to run your code against the test suite.

## Description

The application is API based and it must expose 2 resources: `authors` and `books`

Please find below a description of the resources, the necessary endpoints, JSON payloads and actions that should be performed.

## Entities

### Author
This entity must have the following fields (mapped to database!):
* `id`: integer, auto increment
* `name`: text
* `email`: text
* `booksCount`: integer, default 0

#### JSON format `author:write`
When creating or updating Books, this is the JSON payload that should be used
```json
{
  "name": "Author name",
  "email": "email"
}
```
Both parameters are strings.

#### JSON format `author:read`
When returning an Author from the endpoints, this is the JSON payload that should be used
```json
{
  "name": "Author name",
  "email": "email"
}
```
In this case, authors is not an array if IRIs, but a hydrated collection.

------

### Book
This entity must have the following fields (mapped to database!):
* `id`: integer, auto increment
* `title`: text
* `authors`: ManyToMany relation with Author

#### JSON format `book:write`
When creating or updating Books, this is the JSON payload that should be used
```json
{
  "title": "Test title",
  "authors":  ["/api/authors/1","/api/authors/2"]
}
```
* `title` is a plain string
* `authors` is an array of [IRIs](https://en.wikipedia.org/wiki/Internationalized_Resource_Identifier)

#### JSON format `book:read`
When returning a Book from the endpoints, this is the JSON payload that should be used
```json
{
  "id": 1,
  "title": "Test title",
  "authors":  [
    {
      "id": 1,
      "name": "Author name",
      "booksCount": 1
    },
    {
      "id": 2,
      "name": "Another author name",
      "booksCount": 3
    }
  ]
}
```
In this case, authors is not an array if IRIs, but a hydrated collection.

------

## Endpoints

### POST /api/books
This endpoint should create a new book with the following `book:write` JSON payload format.

The endpoint should return the created resource, with the `book:read` JSON payload format.

### PUT /api/books/{id}
This endpoint should update the book with id {id} with the `book:write` JSON payload format.

The endpoint should return the created resource, with the `book:read` JSON payload format.

### GET /api/books
This endpoint should return all books in the DB with the following JSON payload:
```json
{
  "hydra:member": [{...},{...}],
  "hydra:totalItems": 1
}
```

`hydra:member` contains the array of Book items serialized with the `book:read` format,
whereas `hydra:totalItems` contains the total number of items in the collection.

**NOTE:** This endpoint should also accept an optional query parameter `authors=/api/authors/{id}` that, if present, should make the system return
only the books which have that author in their author collections.

### GET /api/books/{id}
This endpoint returns the book with id {id}, in the `book:read` json format.

------

### POST /api/authors
This endpoint should create a new book with the following `author:write` JSON payload format.

The endpoint should return the created resource, with the `author:read` JSON payload format.

### PUT /api/author/{id}
This endpoint should update the book with id {id} with the `author:write` JSON payload format.

The endpoint should return the created resource, with the `author:read` JSON payload format.

### GET /api/author
This endpoint should return all books in the DB with the following JSON payload:
```json
{
  "hydra:member": [{...},{...}],
  "hydra:totalItems": 1
}
```

`hydra:member` contains the array of Book items serialized with the `author:read` format,
whereas `hydra:totalItems` contains the total number of items in the collection.

### GET /api/author/{id}
This endpoint returns the book with id {id}, in the `author:read` json format.


## Actions

In addition to the standard CRUD endpoints defined above. The system should do the following:

### Author booksCount

Whenever an author is added or removed from a book,
its property bookCount should be updated to reflect the number of books the author currently
is assigned to.


### Author notification

Whenever an author is added or removed from a book, the system should send an email to the author's `email`
to notify them of the change.

Notes:
* You can use arbitrary `from` address and message body, but the `to` address must be the author's email.
* Use Symfony Mailer, because this is what the tests are testing for.
* Keep in mind that authors can be added when a Book is created, and can be added/removed when a Book is updated!# symfony-api
# symfony-api
# symfony-api
