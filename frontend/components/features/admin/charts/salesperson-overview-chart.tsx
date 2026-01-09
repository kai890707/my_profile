'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Legend,
} from 'recharts';
import { Users, TrendingUp } from 'lucide-react';

interface SalespersonOverviewChartProps {
  total: number;
  active: number;
  pending: number;
  totalCompanies: number;
}

export function SalespersonOverviewChart({
  total,
  active,
  pending,
  totalCompanies,
}: SalespersonOverviewChartProps) {
  const data = [
    {
      name: '業務員',
      總數: total,
      活躍: active,
      待審核: pending,
    },
    {
      name: '公司',
      總數: totalCompanies,
      活躍: 0,
      待審核: 0,
    },
  ];

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white px-4 py-3 border border-slate-200 rounded-lg shadow-lg">
          <p className="text-sm font-semibold text-slate-900 mb-2">{label}</p>
          {payload.map((item: any, index: number) => (
            <p key={index} className="text-sm text-slate-600">
              <span style={{ color: item.color }}>{item.name}</span>: {item.value}
            </p>
          ))}
        </div>
      );
    }
    return null;
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <TrendingUp className="h-5 w-5 text-primary-600" />
          平台總覽統計
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={data}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
              <XAxis
                dataKey="name"
                tick={{ fill: '#64748b', fontSize: 14 }}
                tickLine={{ stroke: '#cbd5e1' }}
              />
              <YAxis
                tick={{ fill: '#64748b', fontSize: 12 }}
                tickLine={{ stroke: '#cbd5e1' }}
                allowDecimals={false}
              />
              <Tooltip content={<CustomTooltip />} />
              <Legend wrapperStyle={{ paddingTop: '20px' }} />
              <Bar dataKey="總數" fill="#3b82f6" radius={[8, 8, 0, 0]} />
              <Bar dataKey="活躍" fill="#10b981" radius={[8, 8, 0, 0]} />
              <Bar dataKey="待審核" fill="#f59e0b" radius={[8, 8, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </CardContent>
    </Card>
  );
}
