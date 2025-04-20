
import { Entity, Column, PrimaryGeneratedColumn, OneToMany } from 'typeorm';
import { WpWcOrdersMeta } from './wp_wc_orders_meta.entity';
import { WpWoocommerceOrderItems } from './wp_woocommerce_order_items.entity';

// -- auto-generated definition
// create table wp_wc_orders
// (
//     id                   bigint unsigned auto_increment
//         primary key,
//     status               varchar(20)     null,
//     currency             varchar(10)     null,
//     type                 varchar(20)     null,
//     tax_amount           decimal(26, 8)  null,
//     total_amount         decimal(26, 8)  null,
//     customer_id          bigint unsigned null,
//     billing_email        varchar(320)    null,
//     date_created_gmt     datetime        null,
//     date_updated_gmt     datetime        null,
//     parent_order_id      bigint unsigned null,
//     payment_method       varchar(100)    null,
//     payment_method_title text            null,
//     transaction_id       varchar(100)    null,
//     ip_address           varchar(100)    null,
//     user_agent           text            null,
//     customer_note        text            null
// )
//     collate = utf8mb4_unicode_520_ci;

// create index billing_email
//     on wp_wc_orders (billing_email(191));

// create index customer_id_billing_email
//     on wp_wc_orders (customer_id, billing_email(171));

// create index date_created
//     on wp_wc_orders (date_created_gmt);

// create index date_updated
//     on wp_wc_orders (date_updated_gmt);

// create index parent_order_id
//     on wp_wc_orders (parent_order_id);

// create index status
//     on wp_wc_orders (status);

// create index type_status_date
//     on wp_wc_orders (type, status, date_created_gmt);



@Entity('wp_wc_orders')
export class WpWcOrders {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({ name: 'status', nullable: true })
  status: string;

  @Column({ name: 'currency', nullable: true })
  currency: string;

  @Column({ name: 'type', nullable: true })
  type: string;

  @Column({ name: 'tax_amount', nullable: true })
  taxAmount: number;

  @Column({ name: 'total_amount', nullable: true })
  totalAmount: number;

  @Column({ name: 'customer_id', nullable: true })
  customerId: number;

  @Column({ name: 'billing_email', nullable: true })
  billingEmail: string;

  @Column({ name: 'date_created_gmt', type: 'datetime', nullable: true })
  dateCreatedGmt: Date;

  @Column({ name: 'date_updated_gmt', type: 'datetime', nullable: true })
  dateUpdateGmt: Date;

  @Column({ name: 'parent_order_id', nullable: true })
  parentOrderId: number;

  @Column({ name: 'payment_method', nullable: true })
  paymentMethod: string;


  @Column({ name: 'payment_method_title', nullable: true })
  paymentMethodTitle: string;

  @Column({ name: 'transaction_id', nullable: true })
  transactionId: string;

  @Column({ name: 'ip_address', nullable: true })
  ipAddress: string;

  @Column({ name: 'user_agent', nullable: true })
  userAgent: string;

  @Column({ name: 'customer_note', nullable: true })
  customerNote: string;
  
  @OneToMany(() => WpWcOrdersMeta, (orderMeta) => orderMeta.order)
  orderMeta: WpWcOrdersMeta[];
  
  @OneToMany(() => WpWoocommerceOrderItems, (orderItem) => orderItem.order)
  orderItems: WpWoocommerceOrderItems[];
} 