import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { WpWcOrders } from './entities/wp_wc_orders.entity';
import { WpWcOrdersMeta } from './entities/wp_wc_orders_meta.entity';
import { WpWoocommerceOrderItems } from './entities/wp_woocommerce_order_items.entity';
import { WpWoocommerceOrderItemMeta } from './entities/wp_woocommerce_order_itemmeta.entity';
import { WpWcOrdersService } from './services/wp_wc_orders.service';
import { WpWcOrdersController } from './controllers/wp_wc_orders.controller';

@Module({
  imports: [TypeOrmModule.forFeature([WpWcOrders, WpWcOrdersMeta, WpWoocommerceOrderItems, WpWoocommerceOrderItemMeta])],
  controllers: [WpWcOrdersController],
  providers: [WpWcOrdersService],
  exports: [WpWcOrdersService],
})
export class OrdersModule {} 