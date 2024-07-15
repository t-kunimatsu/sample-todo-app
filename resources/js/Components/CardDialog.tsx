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
  onSave: (title: string) => void;
};

export type FormValues = {
  title: string;
};

const CardDialog: React.FC<CardDialogProps> = ({ onSave }) => {
  const dialogMode = useTodoBoard((state) => state.dialogMode);
  const dialogOpen = useTodoBoard((state) => state.dialogOpen);
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
    <Dialog open={dialogOpen} onClose={handleClose} fullWidth>
      <DialogTitle sx={{ paddingBottom: 0 }}>{dialogMode === "add" ? "追加" : "編集"}</DialogTitle>
      <DialogContent>
        <form onSubmit={handleSubmit(onSubmit)}>
          <TextField
            InputProps={{ disableUnderline: true }}
            variant="standard"
            multiline
            minRows={3}
            autoFocus
            margin="dense"
            // label="やること"
            placeholder="やること"
            type="text"
            fullWidth
            {...register("title", { required: "Title is required" })}
            error={!!errors.title}
            helperText={errors.title?.message}
          />
          <DialogActions>
            <Button onClick={handleClose} color="primary" data-testid={`cancel-button`}>
              キャンセル
            </Button>
            <Button type="submit" color="primary" disabled={!isValid} data-testid={`save-button`}>
              保存
            </Button>
          </DialogActions>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default CardDialog;
