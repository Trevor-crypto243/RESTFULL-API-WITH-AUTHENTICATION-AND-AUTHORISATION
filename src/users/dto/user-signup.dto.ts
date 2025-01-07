import { IsEmail, IsNotEmpty, IsString, MinLength } from "class-validator";
import { UserSignInDTO } from "./user-signin.dto";

export class UserSignUpDTO extends UserSignInDTO{
    @IsNotEmpty({message:'Name can not be null'})
    @IsString({message:'Name should be a string'})
    name:string;
}