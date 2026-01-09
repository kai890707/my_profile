'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip } from 'recharts';
import { Users } from 'lucide-react';

interface SalespersonStatusChartProps {
  active: number;
  pending: number;
  total: number;
}

const COLORS = {
  active: '#10b981', // green-500
  pending: '#f59e0b', // yellow-500
  inactive: '#94a3b8', // slate-400
};

export function SalespersonStatusChart({ active, pending, total }: SalespersonStatusChartProps) {
  const inactive = total - active - pending;

  const data = [
    { name: '活躍業務員', value: active, color: COLORS.active },
    { name: '待審核', value: pending, color: COLORS.pending },
    { name: '未啟用', value: inactive, color: COLORS.inactive },
  ].filter((item) => item.value > 0);

  const CustomTooltip = ({ active, payload }: any) => {
    if (active && payload && payload.length) {
      const data = payload[0];
      const percentage = total > 0 ? ((data.value / total) * 100).toFixed(1) : '0';
      return (
        <div className="bg-white px-4 py-2 border border-slate-200 rounded-lg shadow-lg">
          <p className="text-sm font-semibold text-slate-900">{data.name}</p>
          <p className="text-sm text-slate-600">
            {data.value} 人 ({percentage}%)
          </p>
        </div>
      );
    }
    return null;
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Users className="h-5 w-5 text-primary-600" />
          業務員狀態分佈
        </CardTitle>
      </CardHeader>
      <CardContent>
        {total > 0 ? (
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={data}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${percent ? (percent * 100).toFixed(0) : 0}%`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {data.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip content={<CustomTooltip />} />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        ) : (
          <div className="h-80 flex items-center justify-center">
            <p className="text-slate-500">暫無數據</p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
