import {DataSource, DataSourceOptions} from 'typeorm'
import {config} from 'dotenv'


config()

export const dataSourceOptions:DataSourceOptions={
    type:'postgres',
    host:process.env.DB_HOST,
    port:Number(process.env.DB_PORT),
    username:process.env.DB_USERNAME,
    password:process.env.DB_PASSWWORD,
    database:process.env.DB_DATABASE,
    // entities:['dist/**/*.entity{.ts,.js}'],
    entities: [__dirname + '/../**/*.entity.{js,ts}'],
    migrations:['dist/db/migrations/*{.ts,.js}'],
    logging:true,
    synchronize:true //automatically creates our entities/ automatically modifies database structure if we modify our entities
    //not suitable for production
}

const dataSource = new DataSource(dataSourceOptions)

export default dataSource