import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { WpWcOrders } from '../entities/wp_wc_orders.entity';
import { CreateOrderDto } from '../dto/create-order.dto';
import { UpdateOrderDto } from '../dto/update-order.dto';

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

  async findByCustomerId(customerId: number): Promise<WpWcOrders[]> {
    return this.wcOrdersRepository.find({ 
      where: { customerId }, 
      relations: ['orderItems', 'orderItems.meta'] 
    });
  }

  // update 
  async update(id: number, updateOrderDto: UpdateOrderDto): Promise<WpWcOrders> {
    const order = await this.findOne(id);
    if (!order) {
      throw new NotFoundException('Order not found');
    }
    return this.wcOrdersRepository.save({ ...order, ...updateOrderDto });
  }

  // delete 
  async delete(id: number): Promise<{ affected?: number }> {
    const result = await this.wcOrdersRepository.delete(id);
    return result;
  }

  // post : new order
  async create(createOrderDto: CreateOrderDto): Promise<WpWcOrders> {
    return this.wcOrdersRepository.save(createOrderDto);
  }
} 