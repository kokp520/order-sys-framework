import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn, OneToMany } from 'typeorm';
import { WpWcOrders } from './wp_wc_orders.entity';
import { WpWoocommerceOrderItemMeta } from './wp_woocommerce_order_itemmeta.entity';

// -- auto-generated definition
// create table wp_woocommerce_order_items
// (
//     order_item_id   bigint unsigned auto_increment
//         primary key,
//     order_item_name text                    not null,
//     order_item_type varchar(200) default '' not null,
//     order_id        bigint unsigned         not null
// )
//     collate = utf8mb4_unicode_520_ci;

// create index order_id
//     on wp_woocommerce_order_items (order_id);

@Entity('wp_woocommerce_order_items')
export class WpWoocommerceOrderItems {
  @PrimaryGeneratedColumn({ name: 'order_item_id' })
  orderItemId: number;

  @Column({ name: 'order_item_name', type: 'text' })
  orderItemName: string;

  @Column({ name: 'order_item_type', type: 'varchar', length: 200, default: '' })
  orderItemType: string;

  @Column({ name: 'order_id', type: 'bigint', unsigned: true })
  orderId: number;

  @ManyToOne(() => WpWcOrders, (order) => order.id)
  @JoinColumn({ name: 'order_id' })
  order: WpWcOrders;
  
  @OneToMany(() => WpWoocommerceOrderItemMeta, (meta) => meta.orderItem)
  meta: WpWoocommerceOrderItemMeta[];
} 