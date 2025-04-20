import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { OrdersModule } from './orders/orders.module';
// import { ProductsModule } from './products/products.module';
import { AppController } from './app.controller';
import { AppService } from './app.service';

@Module({
  imports: [
    // db
    TypeOrmModule.forRoot({
      type: 'mysql',
      host: 'localhost',
      port: 3306,
      username: 'root',
      password: '',
      database: 'wordpress',
      autoLoadEntities: true,
      synchronize: false,
    }),
    OrdersModule,
    // ProductsModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {} 