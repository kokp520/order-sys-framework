import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { WpWoocommerceOrderItems } from './wp_woocommerce_order_items.entity';

// -- auto-generated definition
// create table wp_woocommerce_order_itemmeta
// (
//     meta_id       bigint unsigned auto_increment
//         primary key,
//     order_item_id bigint unsigned not null,
//     meta_key      varchar(255)    null,
//     meta_value    longtext        null
// )
//     collate = utf8mb4_unicode_520_ci;

// create index order_item_id
//     on wp_woocommerce_order_itemmeta (order_item_id);

// create index meta_key
//     on wp_woocommerce_order_itemmeta (meta_key(191));

@Entity('wp_woocommerce_order_itemmeta')
export class WpWoocommerceOrderItemMeta {
  @PrimaryGeneratedColumn({ name: 'meta_id' })
  metaId: number;

  @Column({ name: 'order_item_id', type: 'bigint', unsigned: true })
  orderItemId: number;

  @Column({ name: 'meta_key', type: 'varchar', length: 255, nullable: true })
  metaKey: string;

  @Column({ name: 'meta_value', type: 'longtext', nullable: true })
  metaValue: string;

  @ManyToOne(() => WpWoocommerceOrderItems, (orderItem) => orderItem.meta)
  @JoinColumn({ name: 'order_item_id' })
  orderItem: WpWoocommerceOrderItems;
} 