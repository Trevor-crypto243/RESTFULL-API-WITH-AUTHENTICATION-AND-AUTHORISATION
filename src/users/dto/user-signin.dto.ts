import { IsEmail, IsNotEmpty, IsString, MinLength } from "class-validator";

export class UserSignInDTO{
    @IsEmail({},{message:'Should be a valid email'})
    @IsNotEmpty({message:'Email can not be null'})
    email:string;

    @IsNotEmpty({message:'Password can not be null'})
    @MinLength(5,{message:'Password minimum character should be 5'})
    password:string;
}