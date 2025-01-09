NEst Js Backend API
https://www.youtube.com/playlist?list=PLxL5WsbN2Sm6uY31egfjfO540B0KOqrP4
docs.nestjs.com


_________Installation
npm i -g @nestjs/cli
nest new <project_name>

___________Core Concepts
___applications of nest
Http Server -  NestFactory.create()
Microservices - NestFactory.createMicroservice(differnt transport protocols,http, nat)

Standalone application - NestFactory.createApplicationContext()
    -does not have network listeners
    -creating scheduled tasks
    -creating cli tools


______Modules @module
class annotated with @module
Building blocks of a nest app
Connected as in a graph
Root modules
Shared config modules 


_____Decorators @decorator
Modifies behaviour
Adds metadata to classes, functions, properties


____Controllers @controller
-receives incoming request and returns a response


____Providers @Injectable , dependency injection, singleton pattern
-a class that can be injected in other classes as a dependency
-declared as provider in the class it is used


______Request handling flow
__Middlewares .apply(LoggerMiddleware)
-making a reuqest go through a particular stage before handed to the method handler

__Guards @UseGuards
-security checks , i.e if a user is authenticated

__interceptors
run before and after the root handler
Nest interceptor interface
logging, caching

__pipes PipeTransform
validate and transform data

___Exception filters @Catch




______________Large Scale Nest Js App
https://www.youtube.com/watch?v=jOytv6PQxN0



-node server side
-uses typescript
-type orm

Tools
-node js
-postgresql



typeorm.io
npm install typeorm --save
npm install reflect-metadata --save



_________________Nest CLI(Command Line Interface)
Creating and generating
1. Resources - nest g resource 
    -Generates a whole resouce with all the dtos, entities, controllers
2. Modules - nest g mo
3. Controllers nest g co
5. Services - nest g s


_______APP architecture
Ecom
Users
Products
Orders
Shipping info
Category


__________Database Migration
Generate a migration
npm run migration:generate -- db/migrations/initial

Run migration
npm run migration:run



__________________Repository Pattern
Controller for routing
Service layer for the businesss logic


_________Data Validation with class validator and transformer
npm i --save class-validator class-transformer
dto creates class for validation
Auto validation 
  app.useGlobalPipes(new ValidationPipe())



____________Password encoding
npm i bcrypt
npm i jsonwebtoken @types/jsonwebtoken

________Find all and find one method
writing custom queries w query builders

_______Middleware and custom decorator docs.nestjs.com/middleware
creating the middlewares in the middlewares folder
Configuring the middleware in app module

getting current user from any controller
Param decorators, Body decorators
created custom user decorator

_____________Creating custom authentication and authorisation guards  docs.nestjs.com/guards
@Injectable() decorator that implements the canActivate interface
Have single responsibility

They are excecuted after all middleware , but before any interceptor or pipe

Request -> Middleware -> Guards -> Interceptor -> Handler/Controller -> Response


check whether user is signed in - authentication
checking if a user is authorised to access a particular resource, route - authorisation(depends on user roles)

Reflections and metadata





________Improve Authorisation guards with Mixins
