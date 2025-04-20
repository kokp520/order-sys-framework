import { Controller, Get, Param, Put, Delete, Post, Body, HttpStatus, HttpCode, NotFoundException } from '@nestjs/common';
import { WpWcOrdersService } from '../services/wp_wc_orders.service';
import { CreateOrderDto } from '../dto/create-order.dto';
import { UpdateOrderDto } from '../dto/update-order.dto';
import { randomInt } from 'crypto';

@Controller('order')
export class WpWcOrdersController {
  constructor(private readonly wpWcOrdersService: WpWcOrdersService) { }

  @Get()
  @HttpCode(HttpStatus.OK)
  async findAll(): Promise<any> {
    const orders = await this.wpWcOrdersService.findAll();

    if(!orders){
      return {
        code: HttpStatus.NOT_FOUND,
        data: null
      }
    }

    return {
      code: HttpStatus.OK,
      data: orders
    };
  }

  @Get('customer/:customerId')
  @HttpCode(HttpStatus.OK)
  async findByCustomerId(@Param('customerId') customerId: number): Promise<any> {
    const orders = await this.wpWcOrdersService.findByCustomerId(customerId);
    if(!orders){
      return {
        code: HttpStatus.NOT_FOUND,
        data: null
      }
    }
    return {
      code: HttpStatus.OK,
      data: orders
    };
  }

  @Get(':id')
  @HttpCode(HttpStatus.OK)
  async findOne(@Param('id') id: number): Promise<any> {
    try {
      const order = await this.wpWcOrdersService.findOne(id);
      if (!order) {
        return {
          code: HttpStatus.NOT_FOUND,
        };
      }
      return {
        code: HttpStatus.OK,
        data: order
      };
    } catch (error) {
      return {
        code: HttpStatus.INTERNAL_SERVER_ERROR,
      };
    }
  }

  // update 
  @Put(':id')
  @HttpCode(HttpStatus.OK)
  async update(@Param('id') id: number, @Body() updateOrderDto: UpdateOrderDto): Promise<any> {
    try {
      const order = await this.wpWcOrdersService.findOne(id);
      if (!order) {
        return {
          code: HttpStatus.NO_CONTENT,
        };
      }
      
      updateOrderDto.dateUpdateGmt = new Date();
      const updatedOrder = await this.wpWcOrdersService.update(id, updateOrderDto);

      return {
        code: HttpStatus.OK,
        data: updatedOrder
      };
    } catch (error) {
      return {
        code: HttpStatus.INTERNAL_SERVER_ERROR,
      };
    }
  }

  // delete
  @Delete(':id')
  @HttpCode(HttpStatus.OK)
  async delete(@Param('id') id: number): Promise<any> {
    try {
      const order = await this.wpWcOrdersService.findOne(id);
      if (!order) {
        return {
          code: HttpStatus.NO_CONTENT,
        };
      }

      const result = await this.wpWcOrdersService.delete(id);
      if (result && result.affected == 0) {
        return {
          code: HttpStatus.OK,
          data: { id: id, deleted: false, affected: 0 }
        }
      }
      return {
        code: HttpStatus.OK,
        data: { id: id, deleted: true, affected: result.affected }
      };
    } catch (error) {
      return {
        code: HttpStatus.INTERNAL_SERVER_ERROR,
      };
    }
  }

  // post : new order
  @Post()
  @HttpCode(HttpStatus.CREATED)
  async create(@Body() createOrderDto: CreateOrderDto): Promise<any> {
    if (!createOrderDto.customerId) {
      createOrderDto.customerId = randomInt(1, 12000); // random customer id 自定義填充 給予postman測試
    }

    const now = new Date();
    if (!createOrderDto.dateCreatedGmt) {
      createOrderDto.dateCreatedGmt = now;
    }

    if (!createOrderDto.dateUpdateGmt) {
      createOrderDto.dateUpdateGmt = now;
    }

    try {
      const newOrder = await this.wpWcOrdersService.create(createOrderDto);

      return {
        code: HttpStatus.CREATED,
        data: newOrder
      };
    } catch (error) {
      return {
        code: HttpStatus.INTERNAL_SERVER_ERROR,
      };
    }

  }
} 