import { cn } from '@/lib/utils';
import { Loader2 } from 'lucide-react';
import { Slot } from '@radix-ui/react-slot';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'ghost' | 'link';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  isLoading?: boolean;
  asChild?: boolean;
}

export function Button({
  className,
  variant = 'default',
  size = 'default',
  isLoading = false,
  disabled,
  children,
  asChild = false,
  ...props
}: ButtonProps) {
  const sizes = {
    default: 'h-10 px-4 py-2 text-base',
    sm: 'h-8 px-3 py-1 text-sm rounded-lg',
    lg: 'h-14 px-8 py-4 text-lg rounded-xl',
    icon: 'h-10 w-10 p-2',
  };

  // 使用 inline styles 確保漸層和陰影效果
  const getButtonStyle = () => {
    if (disabled || isLoading) {
      return {};
    }

    switch (variant) {
      case 'default':
        return {
          background: 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)',
          boxShadow: '0 10px 25px -5px rgba(14, 165, 233, 0.4), 0 8px 10px -6px rgba(14, 165, 233, 0.4)',
        };
      case 'secondary':
        return {
          background: 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)',
          boxShadow: '0 10px 25px -5px rgba(20, 184, 166, 0.4)',
        };
      default:
        return {};
    }
  };

  const variants = {
    default: 'text-white',
    secondary: 'text-white',
    outline: 'border-2 border-blue-500 text-blue-600 hover:bg-blue-50',
    ghost: 'hover:bg-blue-50 text-blue-600',
    link: 'text-blue-600 underline-offset-4 hover:underline',
  };

  const buttonClassName = cn(
    'inline-flex items-center justify-center rounded-xl font-bold transition-all duration-300 ease-out',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed',
    'hover:brightness-110 hover:-translate-y-0.5',
    'active:translate-y-0',
    variants[variant],
    sizes[size],
    className
  );

  // When asChild is true, use Slot and only render children (no loading support)
  if (asChild) {
    const Component = Slot;
    return (
      <Component
        className={buttonClassName}
        style={getButtonStyle()}
        {...props}
      >
        {children}
      </Component>
    );
  }

  // Regular button with loading support
  return (
    <button
      className={buttonClassName}
      style={getButtonStyle()}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading && <Loader2 className="mr-2 h-5 w-5 animate-spin" />}
      {children}
    </button>
  );
}
