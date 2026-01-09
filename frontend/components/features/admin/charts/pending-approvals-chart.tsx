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
import { Clock } from 'lucide-react';

interface PendingApprovalsChartProps {
  users: number;
  companies: number;
  certifications: number;
  experiences: number;
}

export function PendingApprovalsChart({
  users,
  companies,
  certifications,
  experiences,
}: PendingApprovalsChartProps) {
  const data = [
    { name: '業務員註冊', value: users, fill: '#3b82f6' }, // blue-500
    { name: '公司資訊', value: companies, fill: '#a855f7' }, // purple-500
    { name: '專業證照', value: certifications, fill: '#10b981' }, // green-500
    { name: '工作經驗', value: experiences, fill: '#f59e0b' }, // yellow-500
  ];

  const total = users + companies + certifications + experiences;

  const CustomTooltip = ({ active, payload }: any) => {
    if (active && payload && payload.length) {
      const data = payload[0];
      return (
        <div className="bg-white px-4 py-2 border border-slate-200 rounded-lg shadow-lg">
          <p className="text-sm font-semibold text-slate-900">{data.payload.name}</p>
          <p className="text-sm text-slate-600">{data.value} 項</p>
        </div>
      );
    }
    return null;
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5 text-yellow-600" />
          待審核項目統計
        </CardTitle>
      </CardHeader>
      <CardContent>
        {total > 0 ? (
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data}>
                <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                <XAxis
                  dataKey="name"
                  tick={{ fill: '#64748b', fontSize: 12 }}
                  tickLine={{ stroke: '#cbd5e1' }}
                />
                <YAxis
                  tick={{ fill: '#64748b', fontSize: 12 }}
                  tickLine={{ stroke: '#cbd5e1' }}
                  allowDecimals={false}
                />
                <Tooltip content={<CustomTooltip />} />
                <Bar dataKey="value" radius={[8, 8, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        ) : (
          <div className="h-80 flex items-center justify-center">
            <div className="text-center">
              <div className="inline-flex p-3 bg-green-100 rounded-full mb-3">
                <Clock className="h-8 w-8 text-green-600" />
              </div>
              <p className="text-slate-900 font-semibold">沒有待審核項目</p>
              <p className="text-sm text-slate-500 mt-1">所有項目都已處理完畢</p>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
