import { Controller, Get, Post, Body, Patch, Param, Delete } from '@nestjs/common';
import { UsersService } from './users.service';
import { CreateUserDto } from './dto/create-user.dto';
import { UpdateUserDto } from './dto/update-user.dto';
import { UserSignUpDTO } from './dto/user-signup.dto';
import { UserEntity } from './entities/user.entity';
import { UserSignInDTO } from './dto/user-signin.dto';
import { CurrentUser } from '../utility/decorators/current-user.decorator';




@Controller('users')
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  @Post('signup')
  async signup(@Body() body:UserSignUpDTO):Promise<{user:UserEntity}>{
    return {user:await this.usersService.signup(body)};
  }

  @Post('signin')
  async signin(@Body() body:UserSignInDTO) :Promise<{accessToken: string; user: UserEntity}>{
    const user = await this.usersService.signin(body)
    const accessToken = await this.usersService.accessToken(user);

    return {accessToken,user}
  }

  @Get('me')
  getProfile(@CurrentUser() currentUser:UserEntity){
    return currentUser
    
  }


  @Post()
  create(@Body() createUserDto: CreateUserDto) {
    return this.usersService.create(createUserDto);
  }

  @Get('all')
  async findAll():Promise<UserEntity[]>{
    return await this.usersService.findAll();
  }

  @Get(':id')
  async findOne(@Param('id') id: string) {
    return await this.usersService.findOne(+id);
  }

  @Patch(':id')
  update(@Param('id') id: string, @Body() updateUserDto: UpdateUserDto) {
    return this.usersService.update(+id, updateUserDto);
  }

  @Delete(':id')
  remove(@Param('id') id: string) {
    return this.usersService.remove(+id);
  }
}
