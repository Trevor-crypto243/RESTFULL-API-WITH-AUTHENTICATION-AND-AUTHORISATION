import { BadRequestException, Injectable } from '@nestjs/common';
import { CreateUserDto } from './dto/create-user.dto';
import { UpdateUserDto } from './dto/update-user.dto';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { UserEntity } from './entities/user.entity';
import { UserSignUpDTO } from './dto/user-signup.dto';
import {hash,compare} from 'bcrypt'
import { UserSignInDTO } from './dto/user-signin.dto';
import { sign } from 'jsonwebtoken';


@Injectable()
export class UsersService {
  constructor(@InjectRepository(UserEntity) private usersRepository: Repository<UserEntity>){

  }

  //promises
  async signup(body:UserSignUpDTO):Promise<UserEntity>{
    const userExists =await this.findUserByEmail(body.email)

    if(userExists){
      throw new BadRequestException('Email is already in use.')
    }

    body.password = await hash(body.password,10) //encrypting the password
    const user = this.usersRepository.create(body);
    return await this.usersRepository.save(user)
  }

  async signin(UserSignInDTO:UserSignInDTO){
    // const userExists =await this.findUserByEmail(UserSignInDTO.email)
    const userExists =await this.usersRepository.createQueryBuilder('users').addSelect('users.password').where('users.email=:email',{email:UserSignInDTO.email}).getOne() //customised query


    if(!userExists){
      throw new BadRequestException('User Does not exist. Email is not available.')
    }

    const match_pass = await compare(UserSignInDTO.password,userExists.password)
    if(!match_pass) throw new BadRequestException('Bad Credentials');

    
    delete userExists.password
    return userExists;

  }


  create(createUserDto: CreateUserDto) {
    return 'This action adds a new user';
  }

  findAll() {
    return `This action returns all users`;
  }

  findOne(id: number) {
    return `This action returns a #${id} user`;
  }

  update(id: number, updateUserDto: UpdateUserDto) {
    return `This action updates a #${id} user`;
  }

  remove(id: number) {
    return `This action removes a #${id} user`;
  }

  async findUserByEmail(email:string){
    return await this.usersRepository.findOneBy({email})
  }

  async accessToken(user:UserEntity){
    return sign({
      id:user.id,
      email:user.email
    },process.env.ACCESS_TOKEN_SECRET_KEY,
    {expiresIn:process.env.ACCESS_TOKEN_SECRET_EXPIRY})
  }
}


