import { CreateUserDto } from './dto/create-user.dto';
import { UpdateUserDto } from './dto/update-user.dto';
import { Repository } from 'typeorm';
import { UserEntity } from './entities/user.entity';
import { UserSignUpDTO } from './dto/user-signup.dto';
import { UserSignInDTO } from './dto/user-signin.dto';
export declare class UsersService {
    private usersRepository;
    constructor(usersRepository: Repository<UserEntity>);
    signup(body: UserSignUpDTO): Promise<UserEntity>;
    signin(UserSignInDTO: UserSignInDTO): Promise<UserEntity>;
    create(createUserDto: CreateUserDto): string;
    findAll(): string;
    findOne(id: number): string;
    update(id: number, updateUserDto: UpdateUserDto): string;
    remove(id: number): string;
    findUserByEmail(email: string): Promise<UserEntity>;
}
