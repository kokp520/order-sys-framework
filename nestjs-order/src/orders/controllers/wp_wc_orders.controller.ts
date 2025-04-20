import { Controller, Get, Param, Query } from '@nestjs/common';
import { WpWcOrdersService } from '../services/wp_wc_orders.service';
import { WpWcOrders } from '../entities/wp_wc_orders.entity';

@Controller('order')
export class WpWcOrdersController {
  constructor(private readonly wcOrdersService: WpWcOrdersService) {}

  @Get()
  async findAll(): Promise<WpWcOrders[]> {
    return this.wcOrdersService.findAll();
  }

  @Get('status/:status')
  async findByStatus(@Param('status') status: string): Promise<WpWcOrders[]> {
    return this.wcOrdersService.findByStatus(status);
  }

  @Get('customer/:customerId')
  async findByCustomerId(@Param('customerId') customerId: number): Promise<WpWcOrders[]> {
    return this.wcOrdersService.findByCustomerId(customerId);
  }

  @Get(':id')
  async findOne(@Param('id') id: number): Promise<WpWcOrders> {
    return this.wcOrdersService.findOne(id);
  }
} 