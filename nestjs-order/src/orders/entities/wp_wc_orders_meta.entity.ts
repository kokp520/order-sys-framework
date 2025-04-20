import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { WpWcOrders } from './wp_wc_orders.entity';


// -- auto-generated definition
// create table wp_wc_orders_meta
// (
//     id         bigint unsigned auto_increment
//         primary key,
//     order_id   bigint unsigned null,
//     meta_key   varchar(255)    null,
//     meta_value text            null
// )
//     collate = utf8mb4_unicode_520_ci;

// create index meta_key_value
//     on wp_wc_orders_meta (meta_key(100), meta_value(82));

// create index order_id_meta_key_meta_value
//     on wp_wc_orders_meta (order_id, meta_key(100), meta_value(82));



@Entity('wp_wc_order_meta')
export class WpWcOrdersMeta {
    @PrimaryGeneratedColumn({ name: 'meta_id' })
    metaId: number;

    @Column({ name: 'order_id', type: 'bigint', unsigned: true })
    orderId: number;

    @Column({ name: 'meta_key', type: 'varchar', length: 255, nullable: true })
    metaKey: string;

    @Column({ name: 'meta_value', type: 'longtext', nullable: true })
    metaValue: string;

    @ManyToOne(() => WpWcOrders, (order) => order.orderMeta)
    @JoinColumn({ name: 'order_id' })
    order: WpWcOrders;
} 