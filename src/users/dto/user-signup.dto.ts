import { IsEmail, IsNotEmpty, IsString, MinLength } from "class-validator";

export class UserSignUpDTO{
    @IsNotEmpty({message:'Name can not be null'})
    @IsString({message:'Name should be a string'})
    name:string;
    @IsEmail({},{message:'Should be a valid email'})
    @IsNotEmpty({message:'Email can not be null'})
    email:string;

    @IsNotEmpty({message:'Password can not be null'})
    @MinLength(5,{message:'Password minimum character should be 5'})
    password:string;
}