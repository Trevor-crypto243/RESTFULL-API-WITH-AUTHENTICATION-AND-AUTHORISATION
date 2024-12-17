import { Roles } from "../../utility/common/user-roles.enum";
import { Timestamp } from "typeorm";
export declare class UserEntity {
    id: number;
    name: string;
    email: string;
    password: string;
    roles: Roles;
    createdAt: Timestamp;
    updatedAt: Timestamp;
}
