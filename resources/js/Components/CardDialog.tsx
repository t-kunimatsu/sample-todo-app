import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
} from "@mui/material";
import { SubmitHandler, useForm } from "react-hook-form";
import { useTodoBoard } from "./useTodoBoard";
import { useCallback, useMemo } from "react";

export type DialogMode = "add" | "edit";

type CardDialogProps = {
  open: boolean;
  onSave: (title: string) => void;
};

export type FormValues = {
  title: string;
};

const CardDialog: React.FC<CardDialogProps> = ({ open, onSave }) => {
  const dialogMode = useTodoBoard((state) => state.dialogMode);
  const setDialogOpen = useTodoBoard((state) => state.setDialogOpen);
  const setResetCardDialogForm = useTodoBoard((state) => state.setResetCardDialogForm);

  const {
    register,
    handleSubmit,
    reset,
    formState: { isValid, errors },
  } = useForm<FormValues>();

  useMemo(() => setResetCardDialogForm(reset), [setResetCardDialogForm, reset]);

  const onSubmit: SubmitHandler<FormValues> = useCallback(
    (data) => {
      onSave(data.title);
    },
    [onSave]
  );

  const handleClose = useCallback(() => {
    setDialogOpen(false);
  }, [setDialogOpen]);

  return (
    <Dialog open={open} onClose={handleClose} fullWidth data-testid={"hogehoge"}>
      <DialogTitle>{dialogMode === "add" ? "追加" : "編集"}</DialogTitle>
      <DialogContent>
        <form onSubmit={handleSubmit(onSubmit)}>
          <TextField
            multiline
            autoFocus
            margin="dense"
            label="やること"
            type="text"
            fullWidth
            {...register("title", { required: "Title is required" })}
            error={!!errors.title}
            helperText={errors.title?.message}
          />
          <DialogActions>
            <Button onClick={handleClose} color="primary" data-testid={`dialog-cancel`}>
              キャンセル
            </Button>
            <Button type="submit" color="primary" disabled={!isValid}>
              保存
            </Button>
          </DialogActions>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default CardDialog;
