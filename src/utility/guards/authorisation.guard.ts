import { Injectable, CanActivate, ExecutionContext, UnauthorizedException } from "@nestjs/common";
import { Reflector } from "@nestjs/core";

@Injectable()
export class AuthorizeGuard implements CanActivate{
    constructor(private reflector:Reflector){}

    canActivate(context: ExecutionContext):
     boolean{
        const allowedRoles = this.reflector.get<string[]>('allowedRoles',context.getHandler())    
        const request = context.switchToHttp().getRequest()
        const result = request?.currentUser?.roles.map((role:string)=>allowedRoles?.includes(role)).find((val:boolean)=>val===true)
        console.log("*****************************************The current user roles are", request?.currentUser?.roles)
        
        if(result) return true;
        throw new UnauthorizedException('Sorry, you are not authorized. ')
    }
}