import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Button } from '../button';

describe('Button', () => {
  it('renders with children', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByRole('button')).toHaveTextContent('Click me');
  });

  it('handles click events', async () => {
    const user = userEvent.setup();
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Click me</Button>);

    await user.click(screen.getByRole('button'));
    expect(handleClick).toHaveBeenCalledOnce();
  });

  it('applies default variant styles', () => {
    render(<Button>Default</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('text-white');
  });

  it('applies secondary variant styles', () => {
    render(<Button variant="secondary">Secondary</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('text-white');
  });

  it('applies outline variant styles', () => {
    render(<Button variant="outline">Outline</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('border-2', 'border-blue-500');
  });

  it('applies ghost variant styles', () => {
    render(<Button variant="ghost">Ghost</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('hover:bg-blue-50');
  });

  it('applies small size styles', () => {
    render(<Button size="sm">Small</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('h-8', 'text-sm');
  });

  it('applies large size styles', () => {
    render(<Button size="lg">Large</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('h-14', 'text-lg');
  });

  it('shows loading spinner when isLoading is true', () => {
    render(<Button isLoading>Loading</Button>);
    expect(screen.getByRole('button')).toHaveTextContent('Loading');
    // Check for loader icon (lucide-loader-circle class)
    const button = screen.getByRole('button');
    expect(button.querySelector('.lucide-loader-circle')).toBeInTheDocument();
  });

  it('is disabled when isLoading is true', () => {
    render(<Button isLoading>Loading</Button>);
    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Disabled</Button>);
    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('does not trigger onClick when disabled', async () => {
    const user = userEvent.setup();
    const handleClick = vi.fn();
    render(<Button disabled onClick={handleClick}>Disabled</Button>);

    await user.click(screen.getByRole('button'));
    expect(handleClick).not.toHaveBeenCalled();
  });

  it('supports asChild prop with Link', () => {
    const LinkComponent = ({ children }: { children: React.ReactNode }) => (
      <a href="/test">{children}</a>
    );

    render(
      <Button asChild>
        <LinkComponent>Link Button</LinkComponent>
      </Button>
    );

    const link = screen.getByRole('link');
    expect(link).toHaveTextContent('Link Button');
    expect(link).toHaveAttribute('href', '/test');
  });

  it('applies gradient styles for default variant', () => {
    render(<Button>Gradient</Button>);
    const button = screen.getByRole('button');

    // Check inline styles for gradient
    const style = button.getAttribute('style');
    expect(style).toContain('background');
  });

  it('does not show loading spinner when asChild is true', () => {
    const LinkComponent = ({ children }: { children: React.ReactNode }) => (
      <a href="/test">{children}</a>
    );

    render(
      <Button asChild isLoading>
        <LinkComponent>Link</LinkComponent>
      </Button>
    );

    // When asChild is true, loading state is ignored
    const link = screen.getByRole('link');
    expect(link.querySelector('.lucide-loader-circle')).not.toBeInTheDocument();
  });
});
