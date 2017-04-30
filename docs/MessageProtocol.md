# Message protocol

## Message headers

Each message sent from a client to the endpoint must have a leading header line which defines:

* A flag that identifies the line as message header 
* A number that identifies the version of the message protocol 
* The type of the message (see [message types](#message-types)) (3 bytes)
* The number of the following packages (see [package types](#package-types))

Example:

```
H0100102

means:

HEADER-FLAG | VERSION | MSG-TYPE | PACKAGE COUNT
H           |      01 |      001 |            02 
```
 
So a header always has a length of 8 byte.
 
## Message types

* `001` - Client sends a message
* `002` - Client wants to consume messages
* `003` - Endpoint dispatches a message
* `004` - Client acknowledges a message

## Package headers

Each data package in a message is preceded by a package header with defines:
 
* A flag that identifies the line as package header
* A number that identifies the package type
* The length of the following package content

Example:

```
P01000000000000000000000000000000256

means:

PACKAGE-FLAG | PKG-TYPE | CONTENT-LENGTH (as int)
           P |       01 |                     256
```

So a package header always has a length of 36 byte.

**Please note:** The content length has a length of 32 bytes and is filled up with zeros.

## Package types

### All

| PKG-Type | Meaning                            |
|---------:|------------------------------------|
| 01       |Queue name                          |
| 02       |Message content                     |
| 03       |Message ID                          |
| 04       |Count of message for consumption    |

---

### For messages from client to endpoint 

* `01` - Queue name
* `02` - Message content

### For messages from endpoint to client

* `01` - Queue name
* `02` - Message content
* `03` - Message ID

### For Consumption

* `01` - Queue name
* `04` - Count of messages the client wants to consume from the queue

### For message acknowledgment

* `01` - Queue name
* `03` - Message ID

---

## Full message examples

### Send a message

Client sends "Hello World" for queue "Foo" to endpoint.

```
H0100102
P01000000000000000000000000000000003
Foo
P02000000000000000000000000000000011
Hello World
```

### Consume messages

Client wants to consume 5 messages from queue "Foo".

```
H0100202
P01000000000000000000000000000000003
Foo
P04000000000000000000000000000000001
5
```

### Dispatch a message

Endpoint sends the message above to the client.

```
H0100303
P01000000000000000000000000000000003
Foo
P02000000000000000000000000000000011
Hello World
P03000000000000000000000000000000032
d7e7f68761d34838494b233148b5486c
```

### Acknowledge a message

Client acknowledges the consumed message with ID `d7e7f68761d34838494b233148b5486c`.

```
H0100402
P01000000000000000000000000000000003
Foo
P03000000000000000000000000000000032
d7e7f68761d34838494b233148b5486c
```
