"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.UserSignUpClass = void 0;
const class_validator_1 = require("class-validator");
class UserSignUpClass {
}
exports.UserSignUpClass = UserSignUpClass;
__decorate([
    (0, class_validator_1.IsNotEmpty)({ message: 'Name can not be null' }),
    (0, class_validator_1.IsString)({ message: 'Name should be a string' }),
    __metadata("design:type", String)
], UserSignUpClass.prototype, "name", void 0);
__decorate([
    (0, class_validator_1.IsEmail)({}, { message: 'Should be a valid email' }),
    (0, class_validator_1.IsNotEmpty)({ message: 'Email can not be null' }),
    __metadata("design:type", String)
], UserSignUpClass.prototype, "email", void 0);
__decorate([
    (0, class_validator_1.IsNotEmpty)({ message: 'Password can not be null' }),
    (0, class_validator_1.MinLength)(5, { message: 'Password minimum character should be 5' }),
    __metadata("design:type", String)
], UserSignUpClass.prototype, "password", void 0);
//# sourceMappingURL=user-signup.dto.js.map