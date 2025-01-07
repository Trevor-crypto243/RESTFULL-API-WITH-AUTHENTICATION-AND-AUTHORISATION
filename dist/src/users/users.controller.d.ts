import { UsersService } from './users.service';
import { CreateUserDto } from './dto/create-user.dto';
import { UpdateUserDto } from './dto/update-user.dto';
import { UserSignUpDTO } from './dto/user-signup.dto';
import { UserEntity } from './entities/user.entity';
import { UserSignInDTO } from './dto/user-signin.dto';
export declare class UsersController {
    private readonly usersService;
    constructor(usersService: UsersService);
    signup(body: UserSignUpDTO): Promise<{
        user: UserEntity;
    }>;
    signin(body: UserSignInDTO): Promise<{
        user: UserEntity;
    }>;
    create(createUserDto: CreateUserDto): string;
    findAll(): string;
    findOne(id: string): string;
    update(id: string, updateUserDto: UpdateUserDto): string;
    remove(id: string): string;
}
