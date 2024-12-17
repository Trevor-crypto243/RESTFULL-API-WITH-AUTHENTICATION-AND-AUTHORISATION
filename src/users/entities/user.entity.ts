import { Roles } from "../../utility/common/user-roles.enum";
import { Column, CreateDateColumn, Entity, PrimaryGeneratedColumn, Timestamp, UpdateDateColumn } from "typeorm";

@Entity('users')
export class UserEntity {
    @PrimaryGeneratedColumn()
    id: number
    @Column()
    name:string
    @Column({unique:true})
    email:string
    @Column({select:false}) //should not be selected by default
    password:string
    @Column({type:'enum',enum:Roles,array:true, default:[Roles.USER]})
    roles:Roles
    @CreateDateColumn()
    createdAt:Timestamp
    @UpdateDateColumn()
    updatedAt:Timestamp

}
