import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { WpWcOrders } from '../entities/wp_wc_orders.entity';

@Injectable()
export class WpWcOrdersService {
  constructor(
    @InjectRepository(WpWcOrders)
    private wcOrdersRepository: Repository<WpWcOrders>,
    // @InjectRepository(WcOrderMeta)
    // private wcOrderMetaRepository: Repository<WcOrderMeta>,
  ) { }

  async findAll(): Promise<WpWcOrders[]> {
    return this.wcOrdersRepository.find({ 
      relations: ['orderItems', 'orderItems.meta'] 
    });
  }

  async findOne(id: number): Promise<WpWcOrders> {
    return this.wcOrdersRepository.findOne({ 
      where: { id }, 
      relations: ['orderItems', 'orderItems.meta'] 
    });
  }

  async findByStatus(status: string): Promise<WpWcOrders[]> {
    return this.wcOrdersRepository.find({ 
      where: { status }, 
      relations: ['orderItems', 'orderItems.meta'] 
    });
  }

  async findByCustomerId(customerId: number): Promise<WpWcOrders[]> {
    return this.wcOrdersRepository.find({ 
      where: { customerId }, 
      relations: ['orderItems', 'orderItems.meta'] 
    });
  }

  // async findOrdersByItemMetaKey(metaKey: string, metaValue?: string): Promise<WcOrder[]> {
  //   const queryBuilder = this.wcOrdersRepository
  //     .createQueryBuilder('order')
  //     .leftJoinAndSelect('order.orderItems', 'orderItems')
  //     .leftJoinAndSelect('orderItems.meta', 'itemMeta')
  //     .where('itemMeta.metaKey = :metaKey', { metaKey });
    
  //   if (metaValue) {
  //     queryBuilder.andWhere('itemMeta.metaValue = :metaValue', { metaValue });
  //   }
    
  //   return queryBuilder.getMany();
  // }
} 