import { IsString, IsNumber, IsDate, IsOptional, IsEmail } from 'class-validator';

export class CreateOrderDto {
  @IsString()
  @IsOptional()
  status?: string;

  @IsString()
  @IsOptional()
  currency?: string;

  @IsString()
  @IsOptional()
  type?: string;

  @IsNumber()
  @IsOptional()
  taxAmount?: number;

  @IsNumber()
  @IsOptional()
  totalAmount?: number;

  @IsNumber()
  @IsOptional()
  customerId?: number;

  @IsEmail()
  @IsOptional()
  billingEmail?: string;

  @IsDate()
  @IsOptional()
  dateCreatedGmt?: Date;

  @IsDate()
  @IsOptional()
  dateUpdateGmt?: Date;

  @IsNumber()
  @IsOptional()
  parentOrderId?: number;

  @IsString()
  @IsOptional()
  paymentMethod?: string;

  @IsString()
  @IsOptional()
  paymentMethodTitle?: string;

  @IsString()
  @IsOptional()
  transactionId?: string;

  @IsString()
  @IsOptional()
  ipAddress?: string;

  @IsString()
  @IsOptional()
  userAgent?: string;

  @IsString()
  @IsOptional()
  customerNote?: string;
} 