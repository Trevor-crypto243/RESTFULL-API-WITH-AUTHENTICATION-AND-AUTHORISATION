"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Initial1734432976392 = void 0;
class Initial1734432976392 {
    constructor() {
        this.name = 'Initial1734432976392';
    }
    async up(queryRunner) {
        await queryRunner.query(`ALTER TABLE "users" ADD CONSTRAINT "UQ_97672ac88f789774dd47f7c8be3" UNIQUE ("email")`);
    }
    async down(queryRunner) {
        await queryRunner.query(`ALTER TABLE "users" DROP CONSTRAINT "UQ_97672ac88f789774dd47f7c8be3"`);
    }
}
exports.Initial1734432976392 = Initial1734432976392;
//# sourceMappingURL=1734432976392-initial.js.map